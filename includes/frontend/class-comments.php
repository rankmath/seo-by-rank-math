<?php
/**
 * The class handles the comments functionalities.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Frontend
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Frontend;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\HTML;
use MyThemeShop\Helpers\Str;
use RankMath\Helpers\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Comments class.
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */
class Comments {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'wp_head', 'add_attributes', 99 );
	}

	/**
	 * Add UGC attribute and remove ?replytocom parameters.
	 */
	public function add_attributes() {
		/**
		 * Enable or disable the feature that adds ugc attribute.
		 *
		 * @param bool $remove Whether to remove the parameters.
		 */
		if ( $this->do_filter( 'frontend/add_ugc_attribute', true ) ) {
			$this->filter( 'comment_text', 'add_ugc_attribute' );
			$this->filter( 'get_comment_author_link', 'add_ugc_attribute' );
		}

		/**
		 * Enable or disable the feature that removes the ?replytocom parameters.
		 *
		 * @param bool $remove Whether to remove the parameters.
		 */
		if ( $this->do_filter( 'frontend/remove_reply_to_com', true ) ) {
			$this->filter( 'comment_reply_link', 'remove_reply_to_com' );
			$this->action( 'template_redirect', 'replytocom_redirect', 1 );
		}
	}

	/**
	 * Replace the ?replytocom with #comment-[number] in a link.
	 *
	 * @param  string $link The comment link as a string.
	 * @return string The new link.
	 */
	public function remove_reply_to_com( $link ) {
		return preg_replace( '`href=(["\'])(?:.*(?:\?|&|&#038;)replytocom=(\d+)#respond)`', 'href=$1#comment-$2', $link );
	}

	/**
	 * Redirect the ?replytocom URLs.
	 *
	 * @return bool True when redirect has been done.
	 */
	public function replytocom_redirect() {

		if ( isset( $_GET['replytocom'] ) && is_singular() ) {
			$url          = get_permalink( $GLOBALS['post']->ID );
			$query_string = Security::remove_query_arg_raw( 'replytocom', sanitize_text_field( $_SERVER['QUERY_STRING'] ) );
			if ( ! empty( $query_string ) ) {
				$url .= '?' . $query_string;
			}
			$url .= '#comment-' . sanitize_text_field( $_GET['replytocom'] );
			Helper::redirect( $url, 301 );
			return true;
		}

		return false;
	}

	/**
	 * Add 'ugc' attribute to comment.
	 *
	 * @param  string $text Comment or author link text to add ugc attribute.
	 * @return string
	 */
	public function add_ugc_attribute( $text ) {
		preg_match_all( '/<(a\s[^>]+)>/', $text, $matches );
		if ( empty( $matches ) || empty( $matches[0] ) ) {
			return $text;
		}

		foreach ( $matches[0] as $link ) {
			$attrs        = HTML::extract_attributes( $link );
			$attrs['rel'] = empty( $attrs['rel'] ) ? 'ugc' : ( Str::contains( 'ugc', $attrs['rel'] ) ? $attrs['rel'] : $attrs['rel'] . ' ugc' );

			$new  = '<a' . HTML::attributes_to_string( $attrs ) . '>';
			$text = str_replace( $link, $new, $text );
		}

		return $text;
	}

}
