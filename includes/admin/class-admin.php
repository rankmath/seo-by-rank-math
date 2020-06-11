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

		wp_add_dashboard_widget( 'rank_math_dashboard_widget', esc_html__( 'Rank Math', 'rank-math' ), [ $this, 'render_dashboard_widget' ] );
	}

	/**
	 * Render dashboard widget.
	 */
	public function render_dashboard_widget() {
		?>
		<div id="published-posts" class="activity-block">
			<?php $this->do_action( 'dashboard/widget' ); ?>
		</div>
		<?php
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
	 * @param int $post_id The post id.
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
				$suggestion['url'], $suggestion['title'], $label,
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
