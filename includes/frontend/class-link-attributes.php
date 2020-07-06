<?php
/**
 * The class handles adding of attributes to links to content.
 *
 * @since      1.0.43.2
 * @package    RankMath
 * @subpackage RankMath\Frontend
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Frontend;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Url;
use MyThemeShop\Helpers\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Add Link_Attributes class.
 */
class Link_Attributes {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'wp_head', 'add_attributes', 99 );
	}

	/**
	 * Add nofollow, target, title and alt attributes to link and images.
	 */
	public function add_attributes() {

		// Add rel="nofollow" & target="_blank" for external links.
		$this->add_noopener    = $this->do_filter( 'noopener', true );
		$this->nofollow_link   = Helper::get_settings( 'general.nofollow_external_links' );
		$this->nofollow_image  = Helper::get_settings( 'general.nofollow_image_links' );
		$this->new_window_link = Helper::get_settings( 'general.new_window_external_links' );
		$this->remove_class    = $this->do_filter( 'link/remove_class', false );
		$this->is_dirty        = false;

		if ( $this->nofollow_link || $this->new_window_link || $this->nofollow_image || $this->add_noopener || $this->remove_class ) {
			$this->filter( 'the_content', 'add_link_attributes', 11 );
		}
	}

	/**
	 * Add nofollow and target attributes to link.
	 *
	 * @param  string $content Post content.
	 * @return string
	 */
	public function add_link_attributes( $content ) {
		preg_match_all( '/<(a\s[^>]+)>/', $content, $matches );
		if ( empty( $matches ) || empty( $matches[0] ) ) {
			return $content;
		}

		foreach ( $matches[0] as $link ) {
			$attrs = HTML::extract_attributes( $link );

			if ( ! $this->can_add_attributes( $attrs ) ) {
				continue;
			}

			$attrs = $this->remove_link_class( $attrs );
			$attrs = $this->set_external_attrs( $attrs );

			if ( $this->is_dirty ) {
				$new     = '<a' . HTML::attributes_to_string( $attrs ) . '>';
				$content = str_replace( $link, $new, $content );
			}
		}

		return $content;
	}

	/**
	 * Set rel attribute.
	 *
	 * @param array   $attrs    Array which hold rel attribute.
	 * @param string  $property Property to add.
	 * @param boolean $append   Append or not.
	 */
	private function set_rel_attribute( &$attrs, $property, $append ) {
		if ( empty( $attrs['rel'] ) ) {
			$attrs['rel'] = $property;
			return;
		}

		if ( $append ) {
			$attrs['rel'] .= ' ' . $property;
		}
	}

	/**
	 * Check if we can do anything
	 *
	 * @param array $attrs Array of link attributes.
	 *
	 * @return boolean
	 */
	private function can_add_attributes( $attrs ) {
		// If link has no href attribute or if the link is not valid then we don't need to do anything.
		if ( empty( $attrs['href'] ) || empty( wp_parse_url( $attrs['href'], PHP_URL_HOST ) ) || ( isset( $attrs['role'] ) && 'button' === $attrs['role'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Remove rank-math-link class.
	 *
	 * @since 1.0.44.2
	 *
	 * @param array $attrs Array of link attributes.
	 *
	 * @return array $attrs
	 */
	private function remove_link_class( $attrs ) {
		if ( ! $this->remove_class || empty( $attrs['class'] ) || strpos( $attrs['class'], 'rank-math-link' ) === false ) {
			return $attrs;
		}

		$this->is_dirty = true;
		$attrs['class'] = str_replace( 'rank-math-link', '', $attrs['class'] );

		if ( ! trim( $attrs['class'] ) ) {
			unset( $attrs['class'] );
		}

		return $attrs;
	}

	/**
	 * Set External attributs
	 *
	 * @since 1.0.44.2
	 *
	 * @param array $attrs Array of link attributes.
	 *
	 * @return array $attrs
	 */
	private function set_external_attrs( $attrs ) {
		if ( ! $this->nofollow_link && $this->new_window_link && $this->nofollow_image && $this->add_noopener ) {
			return $attrs;
		}

		// Skip if there is no href or it's a hash link like "#id".
		// Skip if relative link.
		// Skip for same domain ignoring sub-domain if any.
		if ( ! Url::is_external( $attrs['href'] ) ) {
			return $attrs;
		}

		if ( $this->should_add_nofollow( $attrs['href'] ) ) {
			if ( $this->nofollow_link || ( $this->nofollow_image && $this->is_valid_image( $attrs['href'] ) ) ) {
				$this->is_dirty = true;
				$this->set_rel_attribute( $attrs, 'nofollow', ( isset( $attrs['rel'] ) && ! Str::contains( 'dofollow', $attrs['rel'] ) && ! Str::contains( 'nofollow', $attrs['rel'] ) ) );
			}
		}

		if ( $this->new_window_link && ! isset( $attrs['target'] ) ) {
			$this->is_dirty  = true;
			$attrs['target'] = '_blank';
		}

		if ( $this->add_noopener && $this->do_filter( 'noopener/domain', Url::get_domain( $attrs['href'] ) ) ) {
			$this->is_dirty = true;
			$this->set_rel_attribute( $attrs, 'noopener', ( isset( $attrs['rel'] ) && ! Str::contains( 'noopener', $attrs['rel'] ) ) );
		}

		return $attrs;
	}

	/**
	 * Check if we need to add nofollow for this link, based on "nofollow_domains" & "nofollow_exclude_domains"
	 *
	 * @param  string $url Link URL.
	 * @return bool
	 */
	private function should_add_nofollow( $url ) {
		if ( ! $this->nofollow_link && ! $this->nofollow_image ) {
			return false;
		}

		$include_domains = $this->get_nofollow_domains( 'include' );
		$exclude_domains = $this->get_nofollow_domains( 'exclude' );
		$parent_domain   = Url::get_domain( $url );

		// Check if domain is in list.
		if ( ! empty( $include_domains ) ) {
			return Str::contains( $parent_domain, $include_domains );
		}

		// Check if domains is NOT in list.
		if ( ! empty( $exclude_domains ) && Str::contains( $parent_domain, $exclude_domains ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get domain for nofollow
	 *
	 * @param  string $type Type either include or exclude.
	 * @return array
	 */
	private function get_nofollow_domains( $type ) {
		static $rank_math_nofollow_domains;

		if ( isset( $rank_math_nofollow_domains[ $type ] ) ) {
			return $rank_math_nofollow_domains[ $type ];
		}

		$setting = 'include' === $type ? 'nofollow_domains' : 'nofollow_exclude_domains';
		$domains = Helper::get_settings( "general.{$setting}" );
		$domains = Str::to_arr_no_empty( $domains );

		$rank_math_nofollow_domains[ $type ] = empty( $domains ) ? false : join( ';', $domains );

		return $rank_math_nofollow_domains[ $type ];
	}

	/**
	 * Is a valid image url.
	 *
	 * @param string $url Image url.
	 *
	 * @return boolean
	 */
	private function is_valid_image( $url ) {
		foreach ( [ '.jpg', '.jpeg', '.png', '.gif' ] as $ext ) {
			if ( Str::contains( $ext, $url ) ) {
				return true;
			}
		}

		return false;
	}
}
