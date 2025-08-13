<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Runner;
use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Helpers\DB as DB_Helper;
use RankMath\Helpers\Param;
use RankMath\Admin\Admin_Helper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @codeCoverageIgnore
 */
class Admin implements Runner {

	use Hooker;
	use Ajax;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'init', 'flush', 999 );
		$this->filter( 'user_contactmethods', 'update_user_contactmethods' );
		$this->action( 'profile_update', 'profile_update', 10, 3 );
		$this->action( 'admin_footer', 'convert_additional_profile_url_to_textarea' );
		$this->action( 'save_post', 'canonical_check_notice' );
		$this->action( 'cmb2_save_options-page_fields', 'update_is_configured_value', 10, 2 );
		$this->filter( 'action_scheduler_pastdue_actions_check_pre', 'as_exclude_pastdue_actions' );
		$this->filter( 'rank_math/pro_badge', 'offer_icon' );
		$this->filter( 'load_script_translation_file', 'load_script_translation_file', 10, 3 );

		// AJAX.
		$this->ajax( 'search_pages', 'search_pages' );
		$this->ajax( 'is_keyword_new', 'is_keyword_new' );
		$this->ajax( 'save_checklist_layout', 'save_checklist_layout' );
		$this->ajax( 'deactivate_plugins', 'deactivate_plugins' );
	}

	/**
	 * Update user profile.
	 *
	 * @param int   $user_id      The user ID.
	 * @param array $old_user_data Old user data.
	 * @param array $userdata      User data.
	 */
	public function profile_update( $user_id, $old_user_data, $userdata ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		$nonce = Param::post( '_wpnonce', '', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! wp_verify_nonce( $nonce, 'update-user_' . $user_id ) ) {
			return false;
		}

		$twitter                 = Param::post( 'twitter', '', FILTER_SANITIZE_URL );
		$facebook                = Param::post( 'facebook', '', FILTER_SANITIZE_URL );
		$additional_profile_urls = Param::post( 'additional_profile_urls', '' );

		if ( $additional_profile_urls ) {
			$additional_profile_urls = array_map( 'sanitize_url', explode( PHP_EOL, $additional_profile_urls ) );
			$additional_profile_urls = implode( ' ', $additional_profile_urls );
		}

		update_user_meta( $user_id, 'twitter', $twitter );
		update_user_meta( $user_id, 'facebook', $facebook );
		update_user_meta( $user_id, 'additional_profile_urls', $additional_profile_urls );
	}

	/**
	 * Flush the rewrite rules once if the rank_math_flush_rewrite option is set.
	 */
	public function flush() {
		if ( get_option( 'rank_math_flush_rewrite' ) ) {
			flush_rewrite_rules();
			delete_option( 'rank_math_flush_rewrite' );
		}

		if ( 'rank-math' === Param::get( 'page' ) && get_option( 'rank_math_view_modules' ) ) {
			delete_option( 'rank_math_view_modules' );
		}
	}

	/**
	 * Add Facebook and Twitter as user contact methods.
	 *
	 * @param array $contactmethods Current contact methods.
	 * @return array New contact methods with extra items.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 */
	public function update_user_contactmethods( $contactmethods ) {
		$contactmethods['twitter']                 = esc_html__( 'Twitter username (without @)', 'rank-math' );
		$contactmethods['facebook']                = esc_html__( 'Facebook profile URL', 'rank-math' );
		$contactmethods['additional_profile_urls'] = esc_html__( 'Additional profile URLs', 'rank-math' );

		return $contactmethods;
	}

	/**
	 * Display admin header.
	 *
	 * @param bool $show_breadcrumbs Determines whether to show breadcrumbs or not.
	 */
	public function display_admin_header( $show_breadcrumbs = true ) {
		$nav_tabs = new Admin_Header();
		$nav_tabs->display( $show_breadcrumbs );
	}

	/**
	 * Display admin breadcrumbs.
	 */
	public function display_admin_breadcrumbs() {
		$nav_tabs = new Admin_Breadcrumbs();
		$nav_tabs->display();
	}

	/**
	 * Display dashboard tabs.
	 */
	public function display_dashboard_nav() {
		$nav_tabs = new Admin_Dashboard_Nav();
		$nav_tabs->display();
	}

	/**
	 * Show notice when canonical URL is not a valid URL.
	 *
	 * @param int $post_id The post ID.
	 */
	public function canonical_check_notice( $post_id ) {
		$post_type  = get_post_type( $post_id );
		$is_allowed = in_array( $post_type, Helper::get_allowed_post_types(), true );

		if ( ! $is_allowed || Helper::is_autosave() || Helper::is_ajax() || isset( $_REQUEST['bulk_edit'] ) ) { // phpcs:ignore
			return $post_id;
		}

		if ( ! empty( $_POST['rank_math_canonical_url'] ) && false === Param::post( 'rank_math_canonical_url', false, FILTER_VALIDATE_URL ) ) { // phpcs:ignore
			$message = esc_html__( 'The canonical URL you entered does not seem to be a valid URL. Please double check it in the SEO meta box &raquo; Advanced tab.', 'rank-math' );
			Helper::add_notification( $message, [ 'type' => 'error' ] );
		}
	}

	/**
	 * Save checklist layout.
	 */
	public function save_checklist_layout() {

		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'onpage_general' );

		if ( empty( $_POST['layout'] ) || ! is_array( $_POST['layout'] ) ) {
			return;
		}

		$layout  = Param::post( 'layout', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$allowed = [
			'basic'               => 1,
			'advanced'            => 1,
			'title-readability'   => 1,
			'content-readability' => 1,
		];
		$layout  = array_intersect_key( $layout, $allowed );

		update_user_meta( get_current_user_id(), 'rank_math_metabox_checklist_layout', $layout );
		exit;
	}

	/**
	 * Ajax handler to search pages based on the searched string. Used in the Local SEO Settings.
	 */
	public function search_pages() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'general' );

		$term = Param::get( 'term' );
		if ( empty( $term ) ) {
			exit;
		}

		global $wpdb;
		$pages = DB_Helper::get_results(
			$wpdb->prepare(
				"SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_type = 'page' AND post_status = 'publish' AND post_title LIKE %s",
				"%{$wpdb->esc_like( $term )}%"
			),
			ARRAY_A
		);

		$data = [];
		foreach ( $pages as $page ) {
			$data[] = [
				'id'   => $page['ID'],
				'text' => $page['post_title'],
				'url'  => get_permalink( $page['ID'] ),
			];
		}

		wp_send_json( [ 'results' => $data ] );
	}

	/**
	 * Check if the keyword has been used before for another post.
	 */
	public function is_keyword_new() {
		global $wpdb;

		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'onpage_general' );

		$result = [ 'isNew' => true ];
		if ( empty( $_GET['keyword'] ) ) {
			$this->success( $result );
		}

		$keyword     = Param::get( 'keyword', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK );
		$object_id   = Param::get( 'objectID', 0, FILTER_VALIDATE_INT );
		$object_type = Param::get( 'objectType', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
		$column_ids  = [
			'post' => 'ID',
			'term' => 'term_id',
			'user' => 'ID',
		];
		if ( ! in_array( $object_type, [ 'post', 'term', 'user' ], true ) ) {
			$object_type = 'post';
		}

		$main = $wpdb->{$object_type . 's'};
		$meta = $wpdb->{$object_type . 'meta'};

		$query = sprintf( 'select %2$s.%1$s from %2$s inner join %3$s on %2$s.%1$s = %3$s.%4$s_id where ', $column_ids[ $object_type ], $main, $meta, $object_type );
		if ( 'post' === $object_type ) {
			$query .= sprintf( '%s.post_status = \'publish\' and ', $main );
		}
		$query .= sprintf( '%1$s.meta_key = \'rank_math_focus_keyword\' and ( %1$s.meta_value = %2$s OR %1$s.meta_value like %3$s ) and %1$s.%4$s_id != %5$d', $meta, '%s', '%s', $object_type, $object_id );

		$data = DB_Helper::get_row( $wpdb->prepare( $query, $keyword, $wpdb->esc_like( $keyword ) . ',%' ) );

		$result['isNew'] = empty( $data );

		$this->success( $result );
	}

	/**
	 * Get link suggestions for the current post.
	 *
	 * @param  int|WP_Post $post Current post.
	 * @return array
	 */
	public function get_link_suggestions( $post ) {
		global $pagenow;

		if ( 'post-new.php' === $pagenow ) {
			return;
		}

		$output = [];
		$post   = get_post( $post );
		$args   = [
			'post_type'      => $post->post_type,
			'post__not_in'   => [ $post->ID ],
			'posts_per_page' => 5,
			'meta_key'       => 'rank_math_pillar_content',
			'meta_value'     => 'on',
			'tax_query'      => [ 'relation' => 'OR' ],
		];

		$taxonomies = Helper::get_object_taxonomies( $post, 'names' );
		$taxonomies = array_filter( $taxonomies, [ $this, 'is_taxonomy_allowed' ] );

		foreach ( $taxonomies as $taxonomy ) {
			$this->set_term_query( $args, $post->ID, $taxonomy );
		}

		$posts = get_posts( $args );
		foreach ( $posts as $related_post ) {
			$item = [
				'title'          => get_the_title( $related_post->ID ),
				'url'            => get_permalink( $related_post->ID ),
				'post_id'        => $related_post->ID,
				'focus_keywords' => get_post_meta( $related_post->ID, 'rank_math_focus_keyword', true ),
			];

			$item['focus_keywords'] = empty( $item['focus_keywords'] ) ? [] : explode( ',', $item['focus_keywords'] );

			$output[] = $item;
		}

		return $output;
	}

	/**
	 * Is taxonomy allowed
	 *
	 * @param string $taxonomy Taxonomy to check.
	 *
	 * @return bool
	 */
	public function is_taxonomy_allowed( $taxonomy ) {
		$exclude_taxonomies = [ 'post_format', 'product_shipping_class' ];
		if ( Str::starts_with( 'pa_', $taxonomy ) || in_array( $taxonomy, $exclude_taxonomies, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Set term query.
	 *
	 * @param array  $query    Array of query.
	 * @param int    $post_id  Post ID to get terms from.
	 * @param string $taxonomy Taxonomy to get terms for.
	 */
	private function set_term_query( &$query, $post_id, $taxonomy ) {
		$terms = wp_get_post_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		$query['tax_query'][] = [
			'taxonomy' => $taxonomy,
			'field'    => 'term_id',
			'terms'    => $terms,
		];
	}

	/**
	 * Output link suggestions.
	 *
	 * @param  array $suggestions Link items.
	 * @return string
	 */
	public function get_link_suggestions_html( $suggestions ) {
		$output = '<div class="rank-math-link-suggestions-content" data-count="' . count( $suggestions ) . '">';

		$is_use_fk = 'focus_keywords' === Helper::get_settings( 'titles.pt_' . get_post_type() . '_ls_use_fk' );
		foreach ( $suggestions as $suggestion ) {
			$label = $suggestion['title'];
			if ( $is_use_fk && ! empty( $suggestion['focus_keywords'] ) ) {
				$label = $suggestion['focus_keywords'][0];
			}

			$output .= sprintf(
				'<div class="suggestion-item">
					<div class="suggestion-actions">
						<button class="dashicons dashicons-clipboard suggestion-copy" title="%5$s" data-clipboard-text="%2$s"></button>
						<button class="dashicons dashicons-admin-links suggestion-insert" title="%6$s" data-url="%2$s" data-text="%7$s"></button>
					</div>
					<span class="suggestion-title" data-fk=\'%1$s\'><a target="_blank" href="%2$s" title="%3$s">%4$s</a></span>
				</div>',
				esc_attr( wp_json_encode( $suggestion['focus_keywords'] ) ),
				$suggestion['url'],
				$suggestion['title'],
				$label,
				esc_attr__( 'Copy Link URL to Clipboard', 'rank-math' ),
				esc_attr__( 'Insert Link in Content', 'rank-math' ),
				esc_attr( $label )
			);
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Updates the is_configured value.
	 *
	 * @param int    $object_id The ID of the current object.
	 * @param string $cmb_id    The current box ID.
	 */
	public function update_is_configured_value( $object_id, $cmb_id ) {
		if ( 0 !== strpos( $cmb_id, 'rank_math' ) && 0 !== strpos( $cmb_id, 'rank-math' ) ) {
			return;
		}

		Helper::is_configured( true );
	}

	/**
	 * Deactivate plugin.
	 */
	public function deactivate_plugins() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		if ( ! current_user_can( 'activate_plugins' ) ) {
			$this->error( esc_html__( 'You are not authorized to perform this action.', 'rank-math' ) );
		}
		$plugin = Param::post( 'plugin', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
		if ( 'all' !== $plugin ) {
			deactivate_plugins( $plugin );
			die( '1' );
		}

		Importers\Detector::deactivate_all();
		die( '1' );
	}

	/**
	 * Action Scheduler: exclude our actions from the past-due checker.
	 * Since this is a *_pre hook, it replaces the original checker.
	 *
	 * We first do the same check as what ActionScheduler_AdminView->check_pastdue_actions() does,
	 * but then we also count how many of those past-due actions are ours.
	 *
	 * @param null $value Null value.
	 */
	public function as_exclude_pastdue_actions( $value ) {
		$query_args = [
			'date'     => as_get_datetime_object( time() - DAY_IN_SECONDS ),
			'status'   => \ActionScheduler_Store::STATUS_PENDING,
			'per_page' => 1,
		];

		$store               = \ActionScheduler_Store::instance();
		$num_pastdue_actions = (int) $store->query_actions( $query_args, 'count' );

		if ( 0 !== $num_pastdue_actions ) {
			$query_args['group']    = 'rank-math';
			$num_pastdue_rm_actions = (int) $store->query_actions( $query_args, 'count' );

			$num_pastdue_actions -= $num_pastdue_rm_actions;
		}

		$threshold_seconds = (int) apply_filters( 'action_scheduler_pastdue_actions_seconds', DAY_IN_SECONDS );
		$threshhold_min    = (int) apply_filters( 'action_scheduler_pastdue_actions_min', 1 );

		$check = ( $num_pastdue_actions >= $threshhold_min );
		return (bool) apply_filters( 'action_scheduler_pastdue_actions_check', $check, $num_pastdue_actions, $threshold_seconds, $threshhold_min );
	}

	/**
	 * Check and print the Anniversary icon in the header of Rank Math's setting pages.
	 */
	public static function offer_icon() {
		if ( ! current_user_can( 'manage_options' ) || defined( 'RANK_MATH_PRO_FILE' ) ) {
			return;
		}

		// Holiday Season related variables.
		$time                   = time();
		$current_year           = 2022;
		$anniversary_start_time = gmmktime( 17, 00, 00, 10, 30, $current_year ); // 30 Oct.
		$anniversary_end_time   = gmmktime( 17, 00, 00, 11, 30, $current_year ); // 30 Nov.
		$holiday_start_time     = gmmktime( 17, 00, 00, 12, 20, $current_year ); // 20 Dec.
		$holiday_end_time       = gmmktime( 17, 00, 00, 01, 07, 2023 ); // 07 Jan.

		ob_start();
		if (
			( $time > $anniversary_start_time && $time < $anniversary_end_time ) ||
			( $time > $holiday_start_time && $time < $holiday_end_time )
		) { ?>
			<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Header+Offer+Icon&utm_campaign=WP" target="_blank" class="rank-math-tooltip bottom" style="margin-left:5px;">
				🎉
				<span><?php esc_attr_e( 'Exclusive Offer!', 'rank-math' ); ?></span>
			</a>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Code to convert Addiontal Profile URLs from input type text to textarea.
	 */
	public function convert_additional_profile_url_to_textarea() {
		if ( ! Admin_Helper::is_user_edit() ) {
			return;
		}

		$field_description = __( 'Additional Profiles to add in the <code>sameAs</code> Schema property.', 'rank-math' );
		?>
		<script type="text/javascript">
			( function( $ ) {
				$( function() {
					const twitterWrapper = $( '.user-twitter-wrap' );
					twitterWrapper.before( '<tr><th><h2 style="margin: 0;">Rank Math SEO</h2></th><td></td></tr>' );

					const additionalProfileField = $( '#additional_profile_urls' );
					if ( ! additionalProfileField.length ) {
						return
					}

					var $txtarea = $( '<textarea />' );
					$txtarea.attr( 'id', additionalProfileField[0].id );
					$txtarea.attr( 'name', additionalProfileField[0].name );
					$txtarea.attr( 'rows', 5 );
					$txtarea.val( additionalProfileField[0].value.replaceAll( " ", "\n" ) );
					additionalProfileField.replaceWith( $txtarea );

					$( '<p class="description"><?php echo wp_kses_post( $field_description ); ?></p>' ).insertAfter( $txtarea );
				} );
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Function to replace domain with seo-by-rank-math in translation file.
	 *
	 * @param string|false $file   Path to the translation file to load. False if there isn't one.
	 * @param string       $handle Name of the script to register a translation domain to.
	 * @param string       $domain The text domain.
	 */
	public function load_script_translation_file( $file, $handle, $domain ) {
		if ( 'rank-math' !== $domain ) {
			return $file;
		}

		$data                       = explode( '/', $file );
		$data[ count( $data ) - 1 ] = preg_replace( '/rank-math/', 'seo-by-rank-math', $data[ count( $data ) - 1 ], 1 );
		return implode( '/', $data );
	}
}
