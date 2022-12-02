<?php
/**
 * The WordPress helpers.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Post;
use RankMath\Term;
use RankMath\User;
use RankMath\Helper;
use MyThemeShop\Helpers\Str;
use RankMath\Helpers\Security;
use MyThemeShop\Helpers\WordPress as WP_Helper;
use RankMath\Role_Manager\Capability_Manager;
use stdClass;
use WP_Screen;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress class.
 */
trait WordPress {

	/**
	 * Wraps wp_safe_redirect to add header.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $location The path to redirect to.
	 * @param int    $status   Status code to use.
	 */
	public static function redirect( $location, $status = 302 ) {
		wp_safe_redirect( $location, $status, 'Rank Math' );
		exit;
	}

	/**
	 * Whether the current user has a specific capability.
	 *
	 * @codeCoverageIgnore
	 * @see current_user_can()
	 *
	 * @param  string $capability Capability name.
	 * @return boolean Whether the current user has the given capability.
	 */
	public static function has_cap( $capability ) {
		return current_user_can( 'rank_math_' . str_replace( '-', '_', $capability ) );
	}

	/**
	 * Get post meta value.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string  $key     Internal key of the value to get (without prefix).
	 * @param  integer $post_id Post ID of the post to get the value for.
	 * @param  string  $default  Default value to use.
	 * @return mixed
	 */
	public static function get_post_meta( $key, $post_id = 0, $default = '' ) {
		return Post::get_meta( $key, $post_id, $default );
	}

	/**
	 * Get term meta value.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string $key      Internal key of the value to get (without prefix).
	 * @param  mixed  $term     Term to get the meta value for either (string) term name, (int) term ID or (object) term.
	 * @param  string $taxonomy Name of the taxonomy to which the term is attached.
	 * @param  string $default  Default value to use.
	 * @return mixed
	 */
	public static function get_term_meta( $key, $term = 0, $taxonomy = '', $default = '' ) {
		return Term::get_meta( $key, $term, $taxonomy, $default );
	}

	/**
	 * Get user meta value.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string $key  Internal key of the value to get (without prefix).
	 * @param  mixed  $user User to get the meta value for either (int) user ID or (object) user.
	 * @param  string $default  Default value to use.
	 * @return mixed
	 */
	public static function get_user_meta( $key, $user = 0, $default = '' ) {
		return User::get_meta( $key, $user, $default );
	}

	/**
	 * Get admin url.
	 *
	 * @param  string $page Page id.
	 * @param  array  $args Pass arguments to query string.
	 * @return string
	 */
	public static function get_admin_url( $page = '', $args = [] ) {
		$page = $page ? 'rank-math-' . $page : 'rank-math';
		$args = wp_parse_args( $args, [ 'page' => $page ] );

		return Security::add_query_arg_raw( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Get Rank Math Connect URL.
	 *
	 * @since 1.0.19
	 * @return string
	 */
	public static function get_connect_url() {
		$args = [
			'page' => 'rank-math',
			'view' => 'help',
		];
		if ( ! is_multisite() ) {
			return Security::add_query_arg_raw( $args, admin_url( 'admin.php' ) );
		}

		// Makes sure the plugin functions are defined before trying to use them.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		return is_plugin_active_for_network( plugin_basename( RANK_MATH_FILE ) ) ?
			Security::add_query_arg_raw( $args, network_admin_url( 'admin.php' ) ) :
			Security::add_query_arg_raw( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Get Rank Math Dashboard url.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return string
	 */
	public static function get_dashboard_url() {
		$site_type     = get_transient( '_rank_math_site_type' );
		$business_type = [ 'news', 'business', 'webshop', 'otherbusiness' ];

		if ( in_array( $site_type, $business_type, true ) ) {
			return self::get_admin_url( 'options-titles#setting-panel-local' );
		}
		return admin_url( 'admin.php?page=rank-math&view=modules' );
	}

	/**
	 * Get active capabilities.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function get_roles_capabilities() {
		$data = [];
		$caps = Capability_Manager::get()->get_capabilities( true );

		foreach ( WP_Helper::get_roles() as $slug => $role ) {
			self::get_role_capabilities( $slug, $caps, $data );
		}

		return $data;
	}

	/**
	 * Get active capabilities for role.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $slug Role slug.
	 * @param array  $caps Array of capabilities.
	 * @param array  $data Data instance.
	 */
	private static function get_role_capabilities( $slug, $caps, &$data ) {
		$role = get_role( $slug );
		if ( ! $role ) {
			return;
		}

		$slug = esc_attr( $slug );
		foreach ( $caps as $cap ) {
			if ( $role->has_cap( $cap ) ) {
				$data[ $slug ][] = $cap;
			}
		}
	}

	/**
	 * Set capabilities to role.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array $roles Data.
	 */
	public static function set_capabilities( $roles ) {
		$caps = Capability_Manager::get()->get_capabilities( true );
		foreach ( WP_Helper::get_roles() as $slug => $role ) {
			self::set_role_capabilities( $slug, $caps, $roles );
		}
	}

	/**
	 * Set capabilities for role.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $slug  Role slug.
	 * @param array  $caps  Array of capabilities.
	 * @param array  $roles Data.
	 */
	private static function set_role_capabilities( $slug, $caps, $roles ) {
		$role = get_role( $slug );
		if ( ! $role ) {
			return;
		}

		$roles[ $slug ] = isset( $roles[ $slug ] ) && is_array( $roles[ $slug ] ) ? array_flip( $roles[ $slug ] ) : [];
		foreach ( $caps as $cap ) {
			$func = isset( $roles[ $slug ], $roles[ $slug ][ $cap ] ) ? 'add_cap' : 'remove_cap';
			$role->$func( $cap );
		}
	}

	/**
	 * Schedules a rewrite flush to happen.
	 *
	 * @codeCoverageIgnore
	 */
	public static function schedule_flush_rewrite() {
		update_option( 'rank_math_flush_rewrite', 1 );
	}

	/**
	 * Get post thumbnail with fallback as
	 *     1. Post thumbnail.
	 *     2. First image in content.
	 *     3. Facebook image if any
	 *     4. Twitter image if any.
	 *     5. Default open graph image set in option panel.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  int|WP_Post  $post_id Post ID or WP_Post object.
	 * @param  string|array $size    Image size. Accepts any valid image size, or an array of width and height values in pixels.
	 * @return false|array Returns an array (url, width, height, is_intermediate), or false, if no image is available.
	 */
	public static function get_thumbnail_with_fallback( $post_id, $size = 'thumbnail' ) {
		if ( has_post_thumbnail( $post_id ) ) {
			$thumbnail_id     = get_post_thumbnail_id( $post_id );
			$image            = wp_get_attachment_image_src( $thumbnail_id, $size );
			$image['caption'] = $image ? get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) : '';

			return self::validate_image_data( $image );
		}

		preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_the_content(), $matches );
		$matches = array_filter( $matches );
		if ( ! empty( $matches ) ) {
			return [ $matches[1][0], 200, 200 ];
		}

		$fb_image = Helper::get_post_meta( 'facebook_image_id', $post_id );
		$tw_image = Helper::get_post_meta( 'twitter_image_id', $post_id, Helper::get_settings( 'titles.open_graph_image_id' ) );
		$og_image = $fb_image ? $fb_image : $tw_image;
		if ( ! $og_image ) {
			return false;
		}

		$image            = wp_get_attachment_image_src( $og_image, $size );
		$image['caption'] = $image ? get_post_meta( $og_image, '_wp_attachment_image_alt', true ) : '';
		return self::validate_image_data( $image );
	}

	/**
	 * Check if plugin is network active
	 *
	 * @codeCoverageIgnore
	 *
	 * @return boolean
	 */
	public static function is_plugin_active_for_network() {
		if ( ! is_multisite() ) {
			return false;
		}

		// Makes sure the plugin is defined before trying to use it.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active_for_network( plugin_basename( RANK_MATH_FILE ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Helper function to validate & format ISO 8601 duration.
	 *
	 * @param  string $iso8601 Duration which need to be converted to seconds.
	 * @return string
	 *
	 * @since 1.0.21
	 */
	public static function get_formatted_duration( $iso8601 ) {
		$end = substr( $iso8601, -1 );
		if ( ! in_array( $end, [ 'D', 'H', 'M', 'S' ], true ) ) {
			return '';
		}

		// The format starts with the letter P, for "period".
		return ( ! Str::starts_with( 'P', $iso8601 ) ) ? 'PT' . $iso8601 : $iso8601;
	}

	/**
	 * Get robots default.
	 *
	 * @return array
	 */
	public static function get_robots_defaults() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : new stdClass();
		$robots = Helper::get_settings( 'titles.robots_global', [] );

		if ( $screen instanceof WP_Screen ) {
			if ( in_array( $screen->base, [ 'post', 'edit' ], true ) && isset( $screen->post_type ) && Helper::get_settings( "titles.pt_{$screen->post_type}_custom_robots" ) ) {
				$robots = Helper::get_settings( "titles.pt_{$screen->post_type}_robots", [] );
			}

			if ( in_array( $screen->base, [ 'term', 'edit-tags' ], true ) && isset( $screen->taxonomy ) && Helper::get_settings( "titles.tax_{$screen->taxonomy}_custom_robots" ) ) {
				$robots = Helper::get_settings( "titles.tax_{$screen->taxonomy}_robots", [] );
			}

			if ( in_array( $screen->base, [ 'profile', 'user-edit' ], true ) && Helper::get_settings( 'titles.author_custom_robots' ) ) {
				$robots = Helper::get_settings( 'titles.author_robots', [] );
			}
		}

		if ( is_array( $robots ) && ! in_array( 'noindex', $robots, true ) ) {
			$robots[] = 'index';
		}

		return $robots;
	}

	/**
	 * Get advanced robots default.
	 *
	 * @return array
	 */
	public static function get_advanced_robots_defaults() {
		$screen          = function_exists( 'get_current_screen' ) ? get_current_screen() : new stdClass();
		$advanced_robots = Helper::get_settings( 'titles.advanced_robots_global', [] );

		if ( $screen instanceof WP_Screen ) {

			if ( 'post' === $screen->base && Helper::get_settings( "titles.pt_{$screen->post_type}_custom_robots" ) ) {
				$advanced_robots = Helper::get_settings( "titles.pt_{$screen->post_type}_advanced_robots", [] );
			}

			if ( 'term' === $screen->base && Helper::get_settings( "titles.tax_{$screen->taxonomy}_custom_robots" ) ) {
				$advanced_robots = Helper::get_settings( "titles.tax_{$screen->taxonomy}_advanced_robots", [] );
			}

			if ( in_array( $screen->base, [ 'profile', 'user-edit' ], true ) && Helper::get_settings( 'titles.author_custom_robots' ) ) {
				$advanced_robots = Helper::get_settings( 'titles.author_advanced_robots', [] );
			}
		}

		return $advanced_robots;
	}

	/**
	 * Convert timestamp and ISO to date.
	 *
	 * @param string  $value            Value to convert.
	 * @param boolean $include_timezone Whether to include timezone.
	 *
	 * @return string
	 */
	public static function convert_date( $value, $include_timezone = false ) {
		if ( Str::contains( 'T', $value ) ) {
			$value = \strtotime( $value );
		}

		return $include_timezone ? date_i18n( 'Y-m-d H:i-T', $value ) : date_i18n( 'Y-m-d H:i', $value );
	}

	/**
	 * Helper function to convert ISO 8601 duration to seconds.
	 * For example "PT1H12M24S" becomes 5064.
	 *
	 * @param string $iso8601 Duration which need to be converted to seconds.
	 * @return int
	 */
	public static function duration_to_seconds( $iso8601 ) {
		$end = substr( $iso8601, -1 );
		if ( ! in_array( $end, [ 'D', 'H', 'M', 'S' ], true ) ) {
			$iso8601 = $iso8601 . 'S';
		}
		$iso8601 = ! Str::starts_with( 'P', $iso8601 ) ? 'PT' . $iso8601 : $iso8601;

		preg_match( '/^P([0-9]+D|)?T?([0-9]+H|)?([0-9]+M|)?([0-9]+S|)?$/', $iso8601, $matches );
		if ( empty( $matches ) ) {
			return false;
		}

		return array_sum(
			[
				absint( $matches[1] ) * DAY_IN_SECONDS,
				absint( $matches[2] ) * HOUR_IN_SECONDS,
				absint( $matches[3] ) * MINUTE_IN_SECONDS,
				absint( $matches[4] ),
			]
		);
	}

	/**
	 * Id block editor enabled.
	 *
	 * @return bool
	 */
	public static function is_block_editor() {
		// Check WordPress version.
		if ( version_compare( get_bloginfo( 'version' ), '5.0.0', '<' ) ) {
			return false;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

		if ( ! $screen instanceof WP_Screen ) {
			return false;
		}

		if ( method_exists( $screen, 'is_block_editor' ) ) {
			return $screen->is_block_editor();
		}

		if ( 'post' === $screen->base ) {
			return self::use_block_editor_for_post_type( $screen->post_type );
		}

		return false;
	}

	/**
	 * Validate Image data. Remove empty values and add default height and width to image
	 *
	 * @param  array $image The Image data.
	 * @return array Array of image data
	 *
	 * @since 1.0.64
	 */
	private static function validate_image_data( $image ) {
		$image = array_filter( $image );
		if ( empty( $image ) ) {
			return [];
		}

		$image[1] = isset( $image[1] ) ? $image[1] : 200;
		$image[2] = isset( $image[2] ) ? $image[2] : 200;

		return $image;
	}

	/**
	 * Return whether a post type is compatible with the block editor.
	 *
	 * @param string $post_type The post type.
	 *
	 * @return bool Whether the post type can be edited with the block editor.
	 */
	private static function use_block_editor_for_post_type( $post_type ) {
		if ( ! post_type_exists( $post_type ) ) {
			return false;
		}

		if ( ! post_type_supports( $post_type, 'editor' ) ) {
			return false;
		}

		$post_type_object = get_post_type_object( $post_type );
		if ( $post_type_object && ! $post_type_object->show_in_rest ) {
			return false;
		}

		/**
		 * Filter whether a post is able to be edited in the block editor.
		 *
		 * @since 5.0.0
		 *
		 * @param bool   $use_block_editor  Whether the post type can be edited or not. Default true.
		 * @param string $post_type         The post type being checked.
		 */
		return apply_filters( 'use_block_editor_for_post_type', true, $post_type );
	}

	/**
	 * Generate classes.
	 *
	 * @return string
	 */
	public static function classnames() {
		$args = func_get_args();

		$data = array_reduce(
			$args,
			function( $carry, $arg ) {
				if ( is_array( $arg ) ) {
					return array_merge( $carry, $arg );
				}

				$carry[] = $arg;
				return $carry;
			},
			[]
		);

		$classes = array_map(
			function ( $key, $value ) {
				$condition = $value;
				$return    = $key;

				if ( is_int( $key ) ) {
					$condition = null;
					$return    = $value;
				}

				$is_array             = is_array( $return );
				$is_object            = is_object( $return );
				$is_stringable_type   = ! $is_array && ! $is_object;
				$is_stringable_object = $is_object && method_exists( $return, '__toString' );

				if ( ! $is_stringable_type && ! $is_stringable_object ) {
					return null;
				}

				if ( is_null( $condition ) ) {
					return $return;
				}

				return $condition ? $return : null;
			},
			array_keys( $data ),
			array_values( $data )
		);

		$classes = array_filter( $classes );

		return implode( ' ', $classes );
	}

	/**
	 * An helper function get the home_url without the WPML language parameter.
	 *
	 * @param string $path   Path relative to the home URL.
	 * @param string $scheme Scheme to give the home URL context.
	 *
	 * @return string
	 */
	public static function get_home_url( $path = '', $scheme = null ) {
		Sitepress::get()->remove_home_url_filter();
		$home_url = home_url( $path, $scheme );
		Sitepress::get()->restore_home_url_filter();

		return $home_url;
	}
}
