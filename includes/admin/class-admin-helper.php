<?php
/**
 * Admin helper Functions.
 *
 * This file contains functions needed on the admin screens.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Data_Encryption;
use RankMath\Helpers\Security;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Admin_Helper class.
 */
class Admin_Helper {

	/**
	 * Get .htaccess related data.
	 *
	 * @return array
	 */
	public static function get_htaccess_data() {
		if ( ! function_exists( 'get_home_path' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$wp_filesystem = WordPress::get_filesystem();
		$htaccess_file = get_home_path() . '.htaccess';

		return ! $wp_filesystem->exists( $htaccess_file ) ? false : [
			'content'  => $wp_filesystem->get_contents( $htaccess_file ),
			'writable' => $wp_filesystem->is_writable( $htaccess_file ),
		];
	}

	/**
	 * Get tooltip HTML.
	 *
	 * @param string $message Message to show in tooltip.
	 *
	 * @return string
	 */
	public static function get_tooltip( $message ) {
		return '<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span>' . $message . '</span></span>';
	}

	/**
	 * Get admin view file.
	 *
	 * @param string $view View filename.
	 *
	 * @return string Complete path to view
	 */
	public static function get_view( $view ) {
		$view = sanitize_key( $view );
		$view = rank_math()->admin_dir() . "views/{$view}.php";

		if ( ! file_exists( $view ) ) {
			wp_redirect( Helper::get_admin_url() );
			exit;
		}

		return $view;
	}

	/**
	 * Get taxonomies as choices.
	 *
	 * @param array $args (Optional) Arguments passed to filter list.
	 *
	 * @return array|bool
	 */
	public static function get_taxonomies_options( $args = [] ) {
		global $wp_taxonomies;

		$args       = wp_parse_args( $args, [ 'public' => true ] );
		$taxonomies = wp_filter_object_list( $wp_taxonomies, $args, 'and', 'label' );

		return empty( $taxonomies ) ? false : [ 'off' => esc_html__( 'None', 'rank-math' ) ] + $taxonomies;
	}

	/**
	 * Registration data get/update.
	 *
	 * @param array|bool|null $data Array of data to save.
	 *
	 * @return array
	 */
	public static function get_registration_data( $data = null ) {
		$row  = 'rank_math_connect_data';
		$keys = [
			'username',
			'email',
			'api_key',
			'plan',
		];

		// Setter.
		if ( ! is_null( $data ) ) {
			if ( false === $data ) {
				update_option( 'rank_math_registration_skip', 1 );
				return delete_option( $row );
			}

			foreach ( $keys as $key ) {
				if ( isset( $data[ $key ] ) ) {
					$data[ $key ] = Data_Encryption::encrypt( $data[ $key ] );
				}
			}

			update_option( 'rank_math_registration_skip', 1 );
			return update_option( $row, $data );
		}

		// Getter.
		$options = Helper::is_plugin_active_for_network() ? get_blog_option( get_main_site_id(), $row, false ) : get_option( $row, false );
		if ( empty( $options ) ) {
			return false;
		}

		foreach ( $keys as $key ) {
			if ( isset( $options[ $key ] ) ) {
				$options[ $key ] = Data_Encryption::decrypt( $options[ $key ] );
			}
		}

		return $options;
	}

	/**
	 * Get user plan.
	 */
	public static function get_user_plan() {
		$data = self::get_registration_data();

		return $data['plan'];
	}

	/**
	 * Is user plan expire.
	 *
	 * @return boolean
	 */
	public static function is_plan_expired() {
		$data = self::get_registration_data();
		if ( ! isset( $data['plan'] ) ) {
			return true;
		}

		return 'free' === $data['plan'];
	}

	/**
	 * Remove registration data and disconnect from RankMath.com.
	 */
	public static function deregister_user() {
		$registered = self::get_registration_data();
		if ( $registered && isset( $registered['username'] ) && isset( $registered['api_key'] ) ) {
			Api::get()->deactivate_site( $registered['username'], $registered['api_key'] );
			self::get_registration_data( false );
		}
	}

	/**
	 * Check if current page is media list page.
	 *
	 * @return bool
	 */
	public static function is_media_library() {
		global $pagenow;

		return 'upload.php' === $pagenow;
	}

	/**
	 * Check if current page is post list page.
	 *
	 * @return bool
	 */
	public static function is_post_list() {
		global $pagenow;

		return 'edit.php' === $pagenow;
	}

	/**
	 * Check if current page is post create/edit screen.
	 *
	 * @return bool
	 */
	public static function is_post_edit() {
		global $pagenow;

		return 'post.php' === $pagenow || 'post-new.php' === $pagenow;
	}

	/**
	 * Check if current page is term create/edit screen.
	 *
	 * @return bool
	 */
	public static function is_term_edit() {
		global $pagenow;

		return 'term.php' === $pagenow || 'edit-tags.php' === $pagenow;
	}

	/**
	 * Check if current page is user create/edit screen.
	 *
	 * @return bool
	 */
	public static function is_user_edit() {
		global $pagenow;

		return 'profile.php' === $pagenow || 'user-edit.php' === $pagenow;
	}

	/**
	 * Check if current page is user or term create/edit screen.
	 *
	 * @return bool
	 */
	public static function is_term_profile_page() {
		global $pagenow;

		return self::is_term_edit() || self::is_user_edit();
	}

	/**
	 * Get Social Share buttons.
	 *
	 * @codeCoverageIgnore
	 */
	public static function get_social_share() {
		if ( Helper::is_whitelabel() ) {
			return;
		}

		$tw_link = 'https://s.rankmath.com/twitter';
		$fb_link = rawurlencode( 'https://s.rankmath.com/suite-free' );
		/* translators: sitename */
		$tw_message = rawurlencode( sprintf( esc_html__( 'I just installed @RankMathSEO #WordPress Plugin. It looks great! %s', 'rank-math' ), $tw_link ) );
		/* translators: sitename */
		$fb_message = rawurlencode( esc_html__( 'I just installed Rank Math SEO WordPress Plugin. It looks promising!', 'rank-math' ) );

		$tweet_url = Security::add_query_arg(
			[
				'text'     => $tw_message,
				'hashtags' => 'SEO',
			],
			'https://twitter.com/intent/tweet'
		);

		$fb_share_url = Security::add_query_arg(
			[
				'u'       => $fb_link,
				'quote'   => $fb_message,
				'caption' => esc_html__( 'SEO by Rank Math', 'rank-math' ),
			],
			'https://www.facebook.com/sharer/sharer.php'
		);
		?>
		<span class="wizard-share">
			<a href="#" onclick="window.open('<?php echo $tweet_url; ?>', 'sharewindow', 'resizable,width=600,height=300'); return false;" class="share-twitter">
				<span class="dashicons dashicons-twitter"></span> <?php esc_html_e( 'Tweet', 'rank-math' ); ?>
			</a>
			<a href="#" onclick="window.open('<?php echo $fb_share_url; ?>', 'sharewindow', 'resizable,width=600,height=300'); return false;" class="share-facebook">
				<span class="dashicons dashicons-facebook-alt"></span> <?php esc_html_e( 'Share', 'rank-math' ); ?>
			</a>
		</span>
		<?php
	}

	/**
	 * Get product activation URL.
	 *
	 * @param string $redirect_to Redirecto url.
	 *
	 * @return string Activate URL.
	 */
	public static function get_activate_url( $redirect_to = null ) {
		if ( empty( $redirect_to ) ) {
			$redirect_to = Security::add_query_arg_raw(
				[
					'page'  => 'rank-math',
					'view'  => 'help',
					'nonce' => wp_create_nonce( 'rank_math_register_product' ),
				],
				admin_url( 'admin.php' )
			);
		} else {
			$redirect_to = Security::add_query_arg_raw(
				[
					'nonce' => wp_create_nonce( 'rank_math_register_product' ),
				],
				$redirect_to
			);
		}

		$args = [
			'site' => rawurlencode( home_url() ),
			'r'    => rawurlencode( $redirect_to ),
		];

		return apply_filters(
			'rank_math/license/activate_url',
			Security::add_query_arg_raw( $args, 'https://rankmath.com/auth/' ),
			$args
		);
	}

	/**
	 * Check if page is set as Homepage.
	 *
	 * @since 1.0.42
	 *
	 * @return boolean
	 */
	public static function is_home_page() {
		$front_page = (int) get_option( 'page_on_front' );

		return $front_page && self::is_post_edit() && (int) Param::get( 'post' ) === $front_page;
	}

	/**
	 * Check if page is set as Posts Page.
	 *
	 * @since 1.0.43
	 *
	 * @return boolean
	 */
	public static function is_posts_page() {
		$posts_page = (int) get_option( 'page_for_posts' );

		return $posts_page && self::is_post_edit() && (int) Param::get( 'post' ) === $posts_page;
	}

	/**
	 * Get Trends icon <svg> element.
	 *
	 * @return string
	 */
	public static function get_trends_icon_svg() {
		return '<svg viewBox="0 0 610 610"><path d="M18.85,446,174.32,290.48l58.08,58.08L76.93,504a14.54,14.54,0,0,1-20.55,0L18.83,466.48a14.54,14.54,0,0,1,0-20.55Z" style="fill:#4285f4"/><path d="M242.65,242.66,377.59,377.6l-47.75,47.75a14.54,14.54,0,0,1-20.55,0L174.37,290.43l47.75-47.75A14.52,14.52,0,0,1,242.65,242.66Z" style="fill:#ea4335"/><polygon points="319.53 319.53 479.26 159.8 537.34 217.88 377.61 377.62 319.53 319.53" style="fill:#fabb05"/><path d="M594.26,262.73V118.61h0a16.94,16.94,0,0,0-16.94-16.94H433.2a16.94,16.94,0,0,0-12,28.92L565.34,274.71h0a16.94,16.94,0,0,0,28.92-12Z" style="fill:#34a853"/><rect width="610" height="610" style="fill:none"/></svg>';
	}
}
