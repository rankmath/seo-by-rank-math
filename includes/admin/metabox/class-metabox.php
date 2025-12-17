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
			$this->action( 'add_meta_boxes', 'add_main_metabox', 30 );
			$this->action( 'rank_math/admin/enqueue_scripts', 'enqueue' );

			if ( Helper::is_site_editor() ) {
				$this->action( 'enqueue_block_editor_assets', 'enqueue' );
			}

			if ( Helper::has_cap( 'link_builder' ) ) {
				$this->action( 'add_meta_boxes', 'add_link_suggestion_metabox', 30 );
			}

			// Add taxonomy metabox hooks.
			$this->action( 'init', 'add_taxonomy_metabox_hooks', 9999 );

			// Add user metabox hooks.
			$this->action( 'edit_user_profile', 'add_user_profile_metabox' );
			$this->action( 'show_user_profile', 'add_user_profile_metabox' );
		}

		$this->action( 'save_post', 'save_meta', 10, 2 );
		$this->action( 'edit_term', 'save_term_meta', 10, 3 );
		$this->action( 'save_post', 'invalidate_facebook_object_cache', 10, 2 );
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
			wp_enqueue_style(
				'rank-math-metabox',
				rank_math()->plugin_url() . 'assets/admin/css/metabox.css',
				[
					'rank-math-common',
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
	 * Add main metabox.
	 */
	public function add_main_metabox() {
		if ( $this->can_add_metabox() ) {
			return;
		}

		$object_types = $this->screen->get_object_types();

		// Add metabox for post types.
		if ( ! empty( $object_types ) ) {
			add_meta_box(
				$this->metabox_id,
				esc_html__( 'Rank Math SEO', 'rank-math' ),
				[ $this, 'render_main_metabox' ],
				$object_types,
				'normal',
				$this->get_priority(),
				[ '__back_compat_meta_box' => \rank_math_is_gutenberg() ]
			);
		}
	}

	/**
	 * Render main metabox content.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_main_metabox( $post ) {
		// Add nonce for security.
		wp_nonce_field( 'rank_math_save_meta', 'rank_math_metabox_nonce' );

		echo '<div class="rank-math-metabox-wrap rank-math-sidebar-panel">';
		echo '<div id="rank-math-metabox-wrapper"></div>';

		// Add primary term hidden fields.
		$this->render_primary_term_fields();

		echo '</div>';
	}

	/**
	 * Render taxonomy metabox content.
	 *
	 * @param WP_Term $term     Current term object.
	 * @param string  $taxonomy Current taxonomy slug.
	 */
	public function render_taxonomy_metabox( $term, $taxonomy ) {
		// Add nonce for security.
		wp_nonce_field( 'rank_math_save_term_meta', 'rank_math_term_metabox_nonce' );
		?>
		<div class="form-table rank-math-metabox-wrap rank-math-metabox-frame postbox">
			<div id="setting-panel-container-<?php echo esc_attr( $this->metabox_id ); ?>" class="rank-math-sidebar-panel rank-math-tabs">
				<h2 class="rank-math-metabox-frame-title"><?php esc_html_e( 'Rank Math SEO', 'rank-math' ); ?></h2>
				<div id="rank-math-metabox-wrapper"></div>
			</div>
		</div>
		<?php
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

		add_meta_box(
			$this->metabox_id . '_link_suggestions',
			esc_html__( 'Link Suggestions', 'rank-math' ),
			[ $this, 'render_link_suggestion_metabox' ],
			$allowed_post_types,
			'side',
			'default'
		);
	}

	/**
	 * Render link suggestion metabox content.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_link_suggestion_metabox( $post ) {
		echo '<div id="rank-math-link-suggestions-tooltip" class="hidden">';
		echo wp_kses_post( Admin_Helper::get_tooltip( esc_html__( 'Click on the button to copy URL or insert link in content. You can also drag and drop links in the post content.', 'rank-math' ) ) );
		echo '</div>';

		$suggestions = rank_math()->admin->get_link_suggestions( $post );
		if ( empty( $suggestions ) ) {
			echo '<em><small>' . esc_html__( 'We can\'t show any link suggestions for this post. Try selecting categories and tags for this post, and mark other posts as Pillar Content to make them show up here.', 'rank-math' ) . '</small></em>';
			return;
		}

		echo wp_kses_post( rank_math()->admin->get_link_suggestions_html( $suggestions ) );
	}

	/**
	 * Save post meta handler.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta( $post_id, $post ) {
		// Verify nonce.
		$nonce = isset( $_POST['rank_math_metabox_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rank_math_metabox_nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'rank_math_save_meta' ) ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		/**
		 * Hook into save handler for main metabox.
		 *
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post    Post object.
		 */
		$this->do_action( 'metabox/process_fields', $post_id, $post );
	}

	/**
	 * Save term meta handler.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function save_term_meta( $term_id, $tt_id, $taxonomy ) {
		// Verify nonce.
		$nonce = isset( $_POST['rank_math_term_metabox_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rank_math_term_metabox_nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'rank_math_save_term_meta' ) ) {
			return;
		}

		/**
		 * Hook into save handler for taxonomy metabox.
		 *
		 * @param int    $term_id  Term ID.
		 * @param int    $tt_id    Term taxonomy ID.
		 * @param string $taxonomy Taxonomy slug.
		 */
		$this->do_action( 'metabox/process_term_fields', $term_id, $tt_id, $taxonomy );
	}

	/**
	 * Invalidate facebook object cache for the post.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function invalidate_facebook_object_cache( $post_id, $post ) {
		// Check if any Facebook meta fields were updated.
		$facebook_fields = [ 'rank_math_facebook_title', 'rank_math_facebook_image', 'rank_math_facebook_description' ];
		$has_update      = false;

		foreach ( $facebook_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$has_update = true;
				break;
			}
		}

		// Early Bail!
		if ( ! $has_update ) {
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
					'id'           => get_permalink( $post_id ),
					'scrape'       => true,
					'access_token' => $app_id . '|' . $secret,
				],
			]
		);
	}

	/**
	 * Add taxonomy metabox hooks.
	 */
	public function add_taxonomy_metabox_hooks() {
		if ( $this->can_add_metabox() ) {
			return;
		}

		$taxonomies = Helper::get_allowed_taxonomies();
		if ( empty( $taxonomies ) ) {
			return;
		}

		$this->screen->get_object_types();

		// Add metabox for taxonomies.
		foreach ( $taxonomies as $taxonomy ) {
			// For editing existing terms - renders after the table.
			add_action( "{$taxonomy}_edit_form", [ $this, 'render_taxonomy_metabox' ], 10, 2 );
		}
	}

	/**
	 * Add SEO metabox on the User profile page.
	 */
	public function add_user_profile_metabox() {
		if ( $this->can_add_metabox() ) {
			return;
		}

		?>
		<div class="form-table rank-math-metabox-wrap rank-math-metabox-frame postbox">
			<div id="setting-panel-container-<?php echo esc_attr( $this->metabox_id ); ?>" class="rank-math-sidebar-panel rank-math-tabs">
				<h2 class="rank-math-metabox-frame-title"><?php esc_html_e( 'Rank Math SEO', 'rank-math' ); ?></h2>
				<div id="rank-math-metabox-wrapper"></div>
			</div>
		</div>
		<?php
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

	/**
	 * Enqueque scripts common for all builders.
	 */
	private function enqueue_commons() {
		wp_register_style( 'rank-math-editor', rank_math()->plugin_url() . 'assets/admin/css/gutenberg.css', [ 'rank-math-common' ], rank_math()->version );
		wp_register_script( 'rank-math-analyzer', rank_math()->plugin_url() . 'assets/admin/js/analyzer.js', [ 'lodash', 'wp-autop', 'wp-wordcount', 'wp-url' ], rank_math()->version, true );
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
	 * Render primary term hidden fields.
	 */
	private function render_primary_term_fields() {
		/**
		 * Allow disabling the primary term feature.
		 *
		 * @param bool $return True to disable.
		 */
		if ( true === $this->do_filter( 'admin/disable_primary_term', false ) ) {
			return;
		}

		$taxonomies = Helper::get_object_taxonomies( Helper::get_post_type(), 'objects' );
		$taxonomies = wp_filter_object_list( $taxonomies, [ 'hierarchical' => true ], 'and', 'name' );
		foreach ( $taxonomies as $taxonomy ) {
			$value = get_post_meta( get_the_ID(), 'rank_math_primary_' . $taxonomy, true );
			$value = $value ? $value : 0;
			printf(
				'<input type="hidden" id="rank_math_primary_%1$s" name="rank_math_primary_%1$s" value="%2$s" data-primary-term="%1$s" />',
				esc_attr( $taxonomy ),
				esc_attr( $value )
			);
		}
	}
}
