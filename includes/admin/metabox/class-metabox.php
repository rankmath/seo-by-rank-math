<?php
/**
 * The metabox functionality of the plugin.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin\Metabox
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Metabox;

use RankMath\CMB2;
use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Metabox class.
 */
class Metabox implements Runner {

	use Hooker;

	/**
	 * Metabox id.
	 *
	 * @var string
	 */
	private $metabox_id = 'rank_math_metabox';

	/**
	 * Screen object.
	 *
	 * @var Screen
	 */
	private $screen;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		if ( $this->dont_load() ) {
			return;
		}

		$this->screen = new Screen();
		if ( $this->screen->is_loaded() ) {
			$this->action( 'cmb2_admin_init', 'add_main_metabox', 30 );
			$this->action( 'rank_math/admin/enqueue_scripts', 'enqueue' );

			if ( Helper::is_site_editor() ) {
				$this->action( 'enqueue_block_editor_assets', 'enqueue' );
			}

			if ( Helper::has_cap( 'link_builder' ) ) {
				$this->action( 'cmb2_admin_init', 'add_link_suggestion_metabox', 30 );
			}
		}

		$this->action( 'cmb2_' . CMB2::current_object_type() . '_process_fields_' . $this->metabox_id, 'save_meta' );
		$this->action( 'cmb2_save_field', 'invalidate_facebook_object_cache', 10, 4 );
	}

	/**
	 * Enqueue styles and scripts for the metabox.
	 */
	public function enqueue() {
		/**
		 * Allow other plugins to enqueue/dequeue admin styles or scripts before plugin assets.
		 */
		$this->do_action( 'admin/before_editor_scripts' );

		$screen = get_current_screen();
		$js     = rank_math()->plugin_url() . 'assets/admin/js/';

		$this->enqueue_commons();
		$this->screen->enqueue();
		$this->screen->localize();
		$this->enqueue_translation();
		rank_math()->variables->setup();
		rank_math()->variables->setup_json();

		$is_gutenberg = Helper::is_block_editor() && \rank_math_is_gutenberg();
		$is_elementor = 'elementor' === Param::get( 'action' );
		Helper::add_json( 'knowledgegraphType', Helper::get_settings( 'titles.knowledgegraph_type' ) );
		if (
			! $is_gutenberg &&
			! $is_elementor &&
			'rank_math_schema' !== $screen->post_type &&
			'edit-tags' !== $screen->base
		) {
			\CMB2_Hookup::enqueue_cmb_css();
			wp_enqueue_style(
				'rank-math-metabox',
				rank_math()->plugin_url() . 'assets/admin/css/metabox.css',
				[
					'rank-math-common',
					'rank-math-cmb2',
					'rank-math-editor',
					'wp-components',
				],
				rank_math()->version
			);

			wp_enqueue_media();
			wp_enqueue_script(
				'rank-math-editor',
				rank_math()->plugin_url() . 'assets/admin/js/classic.js',
				[
					'clipboard',
					'wp-hooks',
					'moment',
					'wp-date',
					'wp-data',
					'wp-api-fetch',
					'wp-components',
					'wp-element',
					'wp-i18n',
					'wp-url',
					'wp-media-utils',
					'rank-math-common',
					'rank-math-analyzer',
					'wp-block-editor',
					'rank-math-app',
				],
				rank_math()->version,
				true
			);
		}

		$this->do_action( 'enqueue_scripts/assessor' );

		/**
		 * Allow other plugins to enqueue/dequeue admin styles or scripts after plugin assets.
		 */
		$this->do_action( 'admin/editor_scripts', $this->screen );
	}

	/**
	 * Enqueque scripts common for all builders.
	 */
	private function enqueue_commons() {
		wp_register_style( 'rank-math-editor', rank_math()->plugin_url() . 'assets/admin/css/gutenberg.css', [ 'rank-math-common' ], rank_math()->version );
		wp_register_script( 'rank-math-analyzer', rank_math()->plugin_url() . 'assets/admin/js/analyzer.js', [ 'lodash', 'wp-autop', 'wp-wordcount' ], rank_math()->version, true );
	}

	/**
	 * Enqueue translation.
	 */
	private function enqueue_translation() {
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'rank-math-analyzer', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
			wp_set_script_translations( 'rank-math-app', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
		}
	}

	/**
	 * Add main metabox.
	 */
	public function add_main_metabox() {
		if ( $this->can_add_metabox() ) {
			return;
		}

		$cmb = $this->create_metabox();
		$cmb->add_field(
			[
				'id'   => 'setting-panel-container-' . $this->metabox_id,
				'type' => 'meta_tab_container_open',
				'tabs' => [],
			]
		);
		$cmb->add_field(
			[
				'id'         => 'rank_math_metabox_wrapper',
				'type'       => 'raw',
				'content'    => '<div id="rank-math-metabox-wrapper"></div>',
				'save_field' => false,
			]
		);

		/**
		 * Allow disabling the primary term feature.
		 *
		 * @param bool $return True to disable.
		 */
		if ( false === apply_filters_deprecated( 'rank_math/primary_term', [ false ], '1.0.43', 'rank_math/admin/disable_primary_term' )
		&& false === $this->do_filter( 'admin/disable_primary_term', false ) ) {
			$taxonomies = Helper::get_object_taxonomies( Helper::get_post_type(), 'objects' );
			$taxonomies = wp_filter_object_list( $taxonomies, [ 'hierarchical' => true ], 'and', 'name' );
			foreach ( $taxonomies as $taxonomy ) {
				$cmb->add_field(
					[
						'id'         => 'rank_math_primary_' . $taxonomy,
						'type'       => 'hidden',
						'default'    => 0,
						'attributes' => [
							'data-primary-term' => $taxonomy,
						],
					]
				);
			}
		}

		$cmb->add_field(
			[
				'id'   => 'setting-panel-container-close-' . $this->metabox_id,
				'type' => 'tab_container_close',
			]
		);

		CMB2::pre_init( $cmb );
	}

	/**
	 * Add link suggestion metabox.
	 */
	public function add_link_suggestion_metabox() {
		$allowed_post_types = [];
		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			if ( false === Helper::get_settings( 'titles.pt_' . $post_type . '_link_suggestions' ) ) {
				continue;
			}

			$allowed_post_types[] = $post_type;
		}

		// Early bail.
		if ( empty( $allowed_post_types ) ) {
			return;
		}

		$cmb = new_cmb2_box(
			[
				'id'           => $this->metabox_id . '_link_suggestions',
				'title'        => esc_html__( 'Link Suggestions', 'rank-math' ),
				'object_types' => $allowed_post_types,
				'context'      => 'side',
				'priority'     => 'default',
			]
		);

		$cmb->add_field(
			[
				'id'      => $this->metabox_id . '_link_suggestions_tooltip',
				'type'    => 'raw',
				'content' => '<div id="rank-math-link-suggestions-tooltip" class="hidden">' . Admin_Helper::get_tooltip( esc_html__( 'Click on the button to copy URL or insert link in content. You can also drag and drop links in the post content.', 'rank-math' ) ) . '</div>',
			]
		);

		$cmb->add_field(
			[
				'id'        => 'rank_math_social_tabs',
				'type'      => 'raw',
				'file'      => rank_math()->includes_dir() . 'metaboxes/link-suggestions.php',
				'not_found' => '<em><small>' . esc_html__( 'We can\'t show any link suggestions for this post. Try selecting categories and tags for this post, and mark other posts as Pillar Content to make them show up here.', 'rank-math' ) . '</small></em>',
			]
		);

		CMB2::pre_init( $cmb );
	}

	/**
	 * Save post meta handler.
	 *
	 * @param  CMB2 $cmb CMB2 metabox object.
	 */
	public function save_meta( $cmb ) {
		/**
		 * Hook into save handler for main metabox.
		 *
		 * @param CMB2 $cmb CMB2 object.
		 */
		$this->do_action( 'metabox/process_fields', $cmb );
	}

	/**
	 * Invalidate facebook object cache for the post.
	 *
	 * @param string     $field_id The current field id paramater.
	 * @param bool       $updated  Whether the metadata update action occurred.
	 * @param string     $action   Action performed. Could be "repeatable", "updated", or "removed".
	 * @param CMB2_Field $field    This field object.
	 */
	public function invalidate_facebook_object_cache( $field_id, $updated, $action, $field ) {
		// Early Bail!
		if ( ! in_array( $field_id, [ 'rank_math_facebook_title', 'rank_math_facebook_image', 'rank_math_facebook_description' ], true ) || ! $updated ) {
			return;
		}

		$app_id = Helper::get_settings( 'titles.facebook_app_id' );
		$secret = Helper::get_settings( 'titles.facebook_secret' );

		// Early bail!
		if ( ! $app_id || ! $secret ) {
			return;
		}

		wp_remote_post(
			'https://graph.facebook.com/',
			[
				'body' => [
					'id'           => get_permalink( $field->object_id() ),
					'scrape'       => true,
					'access_token' => $app_id . '|' . $secret,
				],
			]
		);
	}

	/**
	 * Create metabox
	 *
	 * @return CMB2
	 */
	private function create_metabox() {
		return new_cmb2_box(
			[
				'id'               => $this->metabox_id,
				'title'            => esc_html__( 'Rank Math SEO', 'rank-math' ),
				'object_types'     => $this->screen->get_object_types(),
				'taxonomies'       => Helper::get_allowed_taxonomies(),
				'new_term_section' => false,
				'new_user_section' => 'add-existing-user',
				'context'          => 'normal',
				'priority'         => $this->get_priority(),
				'cmb_styles'       => false,
				'classes'          => 'rank-math-metabox-wrap' . ( Admin_Helper::is_term_profile_page() ? ' rank-math-metabox-frame postbox' : '' ),
				'mb_callback_args' => [ '__back_compat_meta_box' => \rank_math_is_gutenberg() ],
			]
		);
	}

	/**
	 * Get metabox priority
	 *
	 * @return string
	 */
	private function get_priority() {
		$post_type = Param::get( 'post_type' );
		if ( ! $post_type ) {
			$post_type = get_post_type( Param::get( 'post', 0, FILTER_VALIDATE_INT ) );
		}

		$priority = 'product' === $post_type ? 'default' : 'high';

		/**
		 * Filter: Change metabox priority.
		 */
		return $this->do_filter( 'metabox/priority', $priority );
	}

	/**
	 * Can add metabox
	 *
	 * @return bool
	 */
	private function can_add_metabox() {
		return ! Helper::has_cap( 'onpage_general' ) &&
			! Helper::has_cap( 'onpage_advanced' ) &&
			! Helper::has_cap( 'onpage_snippet' ) &&
			! Helper::has_cap( 'onpage_social' );
	}

	/**
	 * Can load metabox.
	 *
	 * @return bool
	 */
	private function dont_load() {
		return Helper::is_heartbeat() || Helper::is_ajax() ||
			( class_exists( 'Vc_Manager' ) && Param::get( 'vc_action' ) ) ||
			is_network_admin();
	}
}
