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
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @codeCoverageIgnore
 */
class Admin implements Runner {

	use Hooker, Ajax;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'init', 'flush', 999 );
		$this->filter( 'user_contactmethods', 'update_user_contactmethods' );
		$this->action( 'save_post', 'canonical_check_notice' );
		$this->action( 'wp_dashboard_setup', 'add_dashboard_widgets' );
		$this->action( 'cmb2_save_options-page_fields', 'update_is_configured_value', 10, 2 );

		// AJAX.
		$this->ajax( 'is_keyword_new', 'is_keyword_new' );
		$this->ajax( 'save_checklist_layout', 'save_checklist_layout' );
		$this->ajax( 'deactivate_plugins', 'deactivate_plugins' );
	}

	/**
	 * Flush the rewrite rules once if the rank_math_flush_rewrite option is set.
	 */
	public function flush() {
		if ( get_option( 'rank_math_flush_rewrite' ) ) {
			flush_rewrite_rules();
			delete_option( 'rank_math_flush_rewrite' );
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
		$contactmethods['twitter']  = esc_html__( 'Twitter username (without @)', 'rank-math' );
		$contactmethods['facebook'] = esc_html__( 'Facebook profile URL', 'rank-math' );

		return $contactmethods;
	}

	/**
	 * Register dashboard widget.
	 */
	public function add_dashboard_widgets() {
		// Early Bail if action is not registered for the dashboard widget hook.
		if ( ! has_action( 'rank_math/dashboard/widget' ) ) {
			return;
		}

		$icon = '<span class="rank-math-icon"><svg viewBox="0 0 462.03 462.03" xmlns="http://www.w3.org/2000/svg" width="20"><g><path d="m462 234.84-76.17 3.43 13.43 21-127 81.18-126-52.93-146.26 60.97 10.14 24.34 136.1-56.71 128.57 54 138.69-88.61 13.43 21z"></path><path d="m54.1 312.78 92.18-38.41 4.49 1.89v-54.58h-96.67zm210.9-223.57v235.05l7.26 3 89.43-57.05v-181zm-105.44 190.79 96.67 40.62v-165.19h-96.67z"></path></g></svg></span>';

		wp_add_dashboard_widget(
			'rank_math_dashboard_widget',
			$icon . esc_html__( 'Rank Math Overview', 'rank-math' ),
			[ $this, 'render_dashboard_widget' ],
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Render dashboard widget.
	 */
	public function render_dashboard_widget() {
		$this->do_action( 'dashboard/widget' );

		$posts = $this->get_feed();
		?>
		<h3 class="rank-math-blog-title"><?php esc_html_e( 'Latest Blog Posts from Rank Math', 'rank-math' ); ?></h3>
		<?php if ( false === $posts ) : ?>
			<p><?php esc_html_e( 'Error in fetching.', 'rank-math' ); ?></p>
			<?php
			return;
		endif;

		echo '<ul class="rank-math-blog-list">';
		$is_new = time() - strtotime( $posts[0]['date'] ) < 15 * DAY_IN_SECONDS;
		$i      = 0;

		foreach ( $posts as $post ) :
			$i++;
			?>
			<li class="rank-math-blog-post">
				<h4>
					<?php if ( $is_new ) : ?>
						<span class="rank-math-new-badge"><?php esc_html_e( 'NEW', 'rank-math' ); ?></span>
					<?php endif; ?>
					<a target="_blank" href="<?php echo esc_url( $post['link'] ); ?>?utm_source=Plugin&utm_medium=Dashboard%20Widget%20Post%20<?php echo esc_attr( $i ); ?>&utm_campaign=WP">
						<?php echo esc_html( $post['title']['rendered'] ); ?>
					</a>
				</h4>
			</li>
			<?php
			$is_new = false;
		endforeach;
		echo '</ul>';
		?>

		<div class="rank-math-widget-footer">
			<a target="_blank" href="https://rankmath.com/blog/?utm_source=Plugin&utm_medium=Dashboard%20Widget%20Blog&utm_campaign=WP">
				<?php esc_html_e( 'Blog', 'rank-math' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( '(opens in a new window)', 'rank-math' ); ?></span>
				<span aria-hidden="true" class="dashicons dashicons-external"></span>
			</a>
			<a target="_blank" href="https://rankmath.com/kb/?utm_source=Plugin&utm_medium=Dashboard%20Widget%20Help&utm_campaign=WP">
				<?php esc_html_e( 'Help', 'rank-math' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( '(opens in a new window)', 'rank-math' ); ?></span>
				<span aria-hidden="true" class="dashicons dashicons-external"></span>
			</a>
			<?php if ( ! defined( 'RANK_MATH_PRO_FILE' ) ) { ?>
				<a target="_blank" href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Dashboard%20Widget%20PRO&utm_campaign=WP" class="rank-math-widget-go-pro">
					<?php esc_html_e( 'Go Pro', 'rank-math' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( '(opens in a new window)', 'rank-math' ); ?></span>
					<span aria-hidden="true" class="dashicons dashicons-external"></span>
				</a>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Get posts.
	 */
	private function get_feed() {
		$cache_key = 'rank_math_feed_posts';
		$cache     = get_transient( $cache_key );
		if ( false !== $cache ) {
			return $cache;
		}

		$response = wp_remote_get( 'https://rankmath.com/wp-json/wp/v2/posts?per_page=3' );

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			set_transient( $cache_key, [], 2 * HOUR_IN_SECONDS );

			return false;
		}

		$posts = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $posts ) || ! is_array( $posts ) ) {
			set_transient( $cache_key, [], 2 * HOUR_IN_SECONDS );

			return false;
		}

		set_transient( $cache_key, $posts, DAY_IN_SECONDS * 15 );

		return $posts;
	}

	/**
	 * Display admin header.
	 */
	public function display_admin_header() {
		$nav_tabs = new Admin_Header();
		$nav_tabs->display();
	}

	/**
	 * Display admin breadcrumbs.
	 */
	public function display_admin_breadcrumbs() {
		$nav_tabs = new Admin_Breadcrumbs();
		$nav_tabs->display();
	}

	/**
	 * Display dashabord tabs.
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

		if ( ! $is_allowed || Conditional::is_autosave() || Conditional::is_ajax() || isset( $_REQUEST['bulk_edit'] ) ) {
			return $post_id;
		}

		if ( ! empty( $_POST['rank_math_canonical_url'] ) && false === Param::post( 'rank_math_canonical_url', false, FILTER_VALIDATE_URL ) ) {
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

		$keyword     = Param::get( 'keyword' );
		$object_id   = Param::get( 'objectID' );
		$object_type = Param::get( 'objectType' );
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

		$data = $wpdb->get_row( $wpdb->prepare( $query, $keyword, $wpdb->esc_like( $keyword ) . ',%' ) ); // phpcs:ignore

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
						<span class="dashicons dashicons-clipboard suggestion-copy" title="%5$s" data-clipboard-text="%2$s"></span>
						<span class="dashicons dashicons-admin-links suggestion-insert" title="%6$s" data-url="%2$s" data-text="%7$s"></span>
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
		$plugin = Param::post( 'plugin' );
		if ( 'all' !== $plugin ) {
			deactivate_plugins( $plugin );
			die( '1' );
		}

		Importers\Detector::deactivate_all();
		die( '1' );
	}
}
