<?php
/**
 * Global functionality of the plugin.
 *
 * Defines the functionality loaded both on admin and frontend.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Paper\Paper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Meta;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Url;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Common class.
 */
class Common {

	use Hooker, Ajax, Meta;

	/**
	 * Constructor method.
	 */
	public function __construct() {
		$this->action( 'loginout', 'nofollow_link' );
		$this->filter( 'register', 'nofollow_link' );

		// Change Permalink for primary term.
		$this->filter( 'post_type_link', 'post_type_link', 9, 2 );
		$this->filter( 'post_link_category', 'post_link_category', 10, 3 );

		// Reorder categories listing: put primary at the beginning.
		$this->filter( 'get_the_terms', 'reorder_the_terms', 10, 3 );

		add_action( 'wp_ajax_nopriv_rank_math_overlay_thumb', [ $this, 'generate_overlay_thumbnail' ] );

		$this->filter( 'is_protected_meta', 'hide_rank_math_meta', 10, 2 );

		new Auto_Updater();
		new Defaults();
		new Admin_Bar_Menu();
	}

	/**
	 * Add rel="nofollow" to a link.
	 *
	 * @param string $link Link.
	 *
	 * @return string
	 */
	public function nofollow_link( $link ) {
		// Check if link is nofollow already.
		if ( strpos( $link, ' rel="nofollow"' ) || strpos( $link, " rel='nofollow'" ) ) {
			return $link;
		}
		return str_replace( '<a ', '<a rel="nofollow" ', $link );
	}

	/**
	 * Filters the category that gets used in the %category% permalink token.
	 *
	 * @param WP_Term $term  The category to use in the permalink.
	 * @param array   $terms Array of all categories (WP_Term objects) associated with the post.
	 * @param WP_Post $post  The post in question.
	 *
	 * @return WP_Term
	 */
	public function post_link_category( $term, $terms, $post ) {
		$primary_term = $this->get_primary_term( $term->taxonomy, $post->ID );
		if ( false === $primary_term ) {
			return $term;
		}

		$term_ids = array_column( $terms, 'term_id' );
		if ( ! is_object( $primary_term ) || ! in_array( $primary_term->term_id, $term_ids, true ) ) {
			return $term;
		}

		return $primary_term;
	}

	/**
	 * Filters the permalink for a post of a custom post type.
	 *
	 * @param string  $post_link The post's permalink.
	 * @param WP_Post $post      The post in question.
	 *
	 * @return string
	 */
	public function post_type_link( $post_link, $post ) {
		$taxonomies = Helper::get_object_taxonomies( $post->post_type, 'objects' );
		$taxonomies = wp_filter_object_list( $taxonomies, [ 'hierarchical' => true ], 'and', 'name' );

		foreach ( $taxonomies as $taxonomy ) {
			$this->sanitize_post_type_link( $post_link, $post, $taxonomy );
		}

		return $post_link;
	}

	/**
	 * AJAX function to generate overlay image. Used in social thumbnails.
	 */
	public function generate_overlay_thumbnail() {
		$thumbnail_id = Param::request( 'id', 0, FILTER_VALIDATE_INT );
		$type         = Param::request( 'type', 'play' );
		$choices      = Helper::choices_overlay_images();
		if ( ! isset( $choices[ $type ] ) ) {
			die();
		}
		$overlay_image = $choices[ $type ]['url'];
		$image         = wp_get_attachment_image_src( $thumbnail_id, 'large' );
		$position      = $choices[ $type ]['position'];

		if ( ! empty( $image ) ) {
			$this->create_overlay_image( $image[0], $overlay_image, $position );
		}
		die();
	}

	/**
	 * Calculate margins based on position string.
	 *
	 * @param string   $position Position string.
	 * @param resource $image    GD image resource identifier.
	 * @param resource $stamp    GD image resource identifier.
	 *
	 * @return array
	 */
	private function get_position_margins( $position, $image, $stamp ) {
		$margins = [
			'middle_center' => [],
		];

		$margins['middle_center']['top']  = round( abs( imagesy( $image ) - imagesy( $stamp ) ) / 2 );
		$margins['middle_center']['left'] = round( abs( imagesx( $image ) - imagesx( $stamp ) ) / 2 );

		$default_margins = $margins['middle_center'];
		$margins         = $this->do_filter( 'social/overlay_image_positions', $margins, $image, $stamp );

		if ( ! isset( $margins[ $position ] ) ) {
			return $default_margins;
		}

		return $margins[ $position ];
	}

	/**
	 * Reorder terms for a post to put primary category to the beginning.
	 *
	 * @param array|WP_Error $terms    List of attached terms, or WP_Error on failure.
	 * @param int            $post_id  Post ID.
	 * @param string         $taxonomy Name of the taxonomy.
	 *
	 * @return array
	 */
	public function reorder_the_terms( $terms, $post_id, $taxonomy ) {
		/**
		 * Filter: Allow disabling the primary term feature.
		 * 'rank_math/primary_term' is deprecated,
		 * use 'rank_math/admin/disable_primary_term' instead.
		 *
		 * @param bool $return True to disable.
		 */
		if ( true === apply_filters_deprecated( 'rank_math/primary_term', [ false ], '1.0.43', 'rank_math/admin/disable_primary_term' )
			|| true === $this->do_filter( 'admin/disable_primary_term', false ) ) {
			return $terms;
		}

		$post_id = empty( $post_id ) ? $GLOBALS['post']->ID : $post_id;

		// Get Primary Term.
		$primary = absint( Helper::get_post_meta( "primary_{$taxonomy}", $post_id ) );
		if ( ! $primary ) {
			return $terms;
		}

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return [ $primary ];
		}

		$primary_term = null;
		foreach ( $terms as $index => $term ) {
			if ( $primary === $term->term_id ) {
				$primary_term = $term;
				unset( $terms[ $index ] );
				array_unshift( $terms, $primary_term );
				break;
			}
		}

		return $terms;
	}

	/**
	 * Hide rank math meta keys
	 *
	 * @param bool   $protected Whether the key is considered protected.
	 * @param string $meta_key  Meta key.
	 *
	 * @return bool
	 */
	public function hide_rank_math_meta( $protected, $meta_key ) {
		return Str::starts_with( 'rank_math_', $meta_key ) ? true : $protected;
	}

	/**
	 * Filters the permalink for a post of a custom post type.
	 *
	 * @param string  $post_link The post's permalink.
	 * @param WP_Post $post      The post in question.
	 * @param object  $taxonomy  The post taxonomy.
	 */
	private function sanitize_post_type_link( &$post_link, $post, $taxonomy ) {
		$find = "%{$taxonomy}%";
		if ( ! Str::contains( $find, $post_link ) ) {
			return;
		}

		$primary_term = $this->get_primary_term( $taxonomy, $post->ID );
		if ( false !== $primary_term ) {
			// Get the hierachical terms.
			$parents = $this->get_hierarchical_link( $primary_term );

			// Replace the placeholder rewrite tag with hierachical terms.
			$post_link = str_replace( $find, $parents, $post_link );
		}
	}

	/**
	 * Get chain of hierarchical links.
	 *
	 * @param WP_Term $term The term in question.
	 *
	 * @return string
	 */
	private function get_hierarchical_link( $term ) {
		if ( is_wp_error( $term ) ) {
			return $term->slug;
		}

		$chain = [];
		$name  = $term->slug;
		if ( $term->parent && ( $term->parent !== $term->term_id ) ) {
			$chain[] = $this->get_hierarchical_link( get_term( $term->parent, $term->taxonomy ) );
		}

		$chain[] = $name;
		return implode( '/', $chain );
	}

	/**
	 * Get primary term of the post.
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @param int    $post_id  Post ID.
	 *
	 * @return object|false Primary term on success, false if there are no terms, WP_Error on failure.
	 */
	private function get_primary_term( $taxonomy, $post_id ) {
		$primary = Helper::get_post_meta( "primary_{$taxonomy}", $post_id );
		if ( ! $primary ) {
			return false;
		}

		$primary = get_term( $primary, $taxonomy );
		return is_wp_error( $primary ) || empty( $primary ) ? false : $primary;
	}

	/**
	 * Get correct imagecreatef based on image file.
	 *
	 * @param string $image_file
	 * @return void
	 */
	private function get_imagecreatefrom_method( $image_file ) {
		$image_format = pathinfo( $image_file, PATHINFO_EXTENSION );
		if ( ! in_array( $image_format, [ 'jpg', 'jpeg', 'gif', 'png' ], true ) ) {
			return '';
		}
		if ( 'jpg' === $image_format ) {
			$image_format = 'jpeg';
		}

		return 'imagecreatefrom' . $image_format;
	}

	/**
	 * Create Overlay Image.
	 *
	 * @param string $image_file    The permalink generated for this post by WordPress.
	 * @param string $overlay_image The ID of the post.
	 */
	private function create_overlay_image( $image_file, $overlay_image, $position ) {
		$imagecreatefrom         = $this->get_imagecreatefrom_method( $image_file );
		$overlay_imagecreatefrom = $this->get_imagecreatefrom_method( $overlay_image );
		if ( ! $imagecreatefrom || ! $overlay_imagecreatefrom ) {
			return;
		}

		$stamp = $overlay_imagecreatefrom( $overlay_image );
		$image = $imagecreatefrom( $image_file );

		if ( ! $image || ! $stamp ) {
			return;
		}

		$stamp_width  = imagesx( $stamp );
		$stamp_height = imagesy( $stamp );

		$img_width  = imagesx( $image );
		$img_height = imagesy( $image );

		if ( $stamp_width > $img_width ) {
			$stamp = imagescale( $stamp, $img_width );
		}

		$margins = $this->get_position_margins( $position, $image, $stamp );

		// Copy the stamp image onto our photo using the margin offsets and the photo width to calculate positioning of the stamp.
		imagecopy( $image, $stamp, $margins['left'], $margins['top'], 0, 0, $stamp_width, $stamp_height );

		// Output and free memory.
		header( 'Content-type: image/png' );
		imagepng( $image );
		imagedestroy( $image );
	}
}
