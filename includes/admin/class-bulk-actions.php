<?php
/**
 * The admin post columns functionality.
 *
 * @since      1.0.212
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Helpers\Url;
use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Post_Columns class.
 */
class Bulk_Actions implements Runner {

	use Hooker;

	/**
	 * SEO data.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'current_screen', 'init' );
	}

	/**
	 * Intialize.
	 */
	public function init() {
		if ( ! Helper::has_cap( 'onpage_general' ) || ! $this->can_add() ) {
			return;
		}

		$this->register_post_columns();
		$this->action( 'admin_enqueue_scripts', 'enqueue' );
	}

	/**
	 * Register post column hooks.
	 */
	private function register_post_columns() {
		$post_types = Helper::get_allowed_post_types();
		foreach ( $post_types as $post_type ) {
			$this->filter( "bulk_actions-edit-{$post_type}", 'post_bulk_actions' );
		}

		$taxonomies = Helper::get_accessible_taxonomies();
		unset( $taxonomies['post_format'] );
		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );
		foreach ( $taxonomies as $taxonomy => $label ) {
			$this->filter( "bulk_actions-edit-{$taxonomy}", 'post_bulk_actions' );
		}
	}

	/**
	 * Add bulk actions for applicable posts, pages, CPTs.
	 *
	 * @param  array $actions Actions.
	 * @return array             New actions.
	 */
	public function post_bulk_actions( $actions ) {
		$new_actions = [ 'rank_math_options' => __( '&#8595; Rank Math', 'rank-math' ) ];

		if ( Helper::has_cap( 'onpage_advanced' ) ) {
			$new_actions['rank_math_bulk_robots_noindex']   = __( 'Set to noindex', 'rank-math' );
			$new_actions['rank_math_bulk_robots_index']     = __( 'Set to index', 'rank-math' );
			$new_actions['rank_math_bulk_robots_nofollow']  = __( 'Set to nofollow', 'rank-math' );
			$new_actions['rank_math_bulk_robots_follow']    = __( 'Set to follow', 'rank-math' );
			$new_actions['rank_math_bulk_remove_canonical'] = __( 'Remove custom canonical URL', 'rank-math' );

			if ( Helper::is_module_active( 'redirections' ) && Helper::has_cap( 'redirections' ) ) {
				$new_actions['rank_math_bulk_redirect']      = __( 'Redirect', 'rank-math' );
				$new_actions['rank_math_bulk_stop_redirect'] = __( 'Remove redirection', 'rank-math' );
			}
		}

		if ( Helper::is_module_active( 'rich-snippet' ) && Helper::has_cap( 'onpage_snippet' ) ) {
			$new_actions['rank_math_bulk_schema_none'] = __( 'Set Schema: None', 'rank-math' );
			$post_type                                 = Param::get( 'post_type', get_post_type() );
			$post_type_default                         = Helper::get_settings( 'titles.pt_' . $post_type . '_default_rich_snippet' );

			if ( $post_type_default ) {
				// Translators: placeholder is the default Schema type setting.
				$new_actions['rank_math_bulk_schema_default'] = sprintf( __( 'Set Schema: Default (%s)', 'rank-math' ), $post_type_default );
			}
		}

		if ( is_array( $actions ) && count( $new_actions ) > 1 ) {
			return array_merge( $actions, $new_actions );
		}

		return $actions;
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function enqueue() {
		$screen = get_current_screen();
		wp_enqueue_style( 'rank-math-post-bulk-edit', rank_math()->plugin_url() . 'assets/admin/css/post-list.css', [ 'wp-components' ], rank_math()->version );

		$allow_editing = Helper::get_settings( 'titles.pt_' . $screen->post_type . '_bulk_editing', true );
		if ( ! $allow_editing || 'readonly' === $allow_editing ) {
			return;
		}

		wp_enqueue_script( 'rank-math-post-bulk-edit', rank_math()->plugin_url() . 'assets/admin/js/post-list.js', [ 'lodash', 'wp-element', 'wp-components' ], rank_math()->version, true );
		Helper::add_json( 'isUserRegistered', Helper::is_site_connected() );
		Helper::add_json( 'contentAICredits', Helper::get_content_ai_credits() );
		Helper::add_json( 'contentAIPlan', Helper::get_content_ai_plan() );
		Helper::add_json( 'isProActive', defined( 'RANK_MATH_PRO_FILE' ) );
		Helper::add_json( 'connectSiteUrl', Admin_Helper::get_activate_url( Url::get_current_url() ) );
	}

	/**
	 * Whether to add Bulk actions on the page.
	 */
	private function can_add() {
		global $pagenow;
		if ( 'edit-tags.php' === $pagenow ) {
			return Helper::get_settings( 'titles.tax_' . Param::get( 'taxonomy' ) . '_add_meta_box' );
		}

		if ( Admin_Helper::is_post_list() || Admin_Helper::is_media_library() ) {
			$screen = get_current_screen();

			$allowed_post_types   = Helper::get_allowed_post_types();
			$allowed_post_types[] = 'attachment';

			return in_array( $screen->post_type, $allowed_post_types, true );
		}

		return false;
	}
}
