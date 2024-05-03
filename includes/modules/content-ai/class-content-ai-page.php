<?php
/**
 * The Role Manager Module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Content_AI_Page
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ContentAI;

use RankMath\ContentAI\Content_AI;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Admin\Page;
use RankMath\Helpers\Param;
use WP_Block_Editor_Context;

defined( 'ABSPATH' ) || exit;

/**
 * Content_AI_Page class.
 */
class Content_AI_Page {
	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		$this->action( 'rank_math/admin/editor_scripts', 'enqueue' );
		$this->action( 'init', 'init' );
		$this->filter( 'wp_insert_post_data', 'remove_unused_generated_content' );
		$this->filter( 'rank_math/database/tools', 'add_tools' );
		$this->filter( 'rank_math/tools/content_ai_cancel_bulk_edit_process', 'cancel_bulk_edit_process' );

		if ( Param::get( 'page' ) !== 'rank-math-content-ai-page' ) {
			return;
		}

		$this->action( 'rank_math/before_help_link', 'add_credits_remaining' );
		$this->action( 'admin_footer', 'content_editor_settings' );
		add_filter( 'should_load_block_editor_scripts_and_styles', '__return_true' );
	}

	/**
	 * Init function.
	 */
	public function init() {
		$this->register_post_type();
		$this->register_admin_page();
		Block_Command::get();
		Bulk_Edit_SEO_Meta::get();
		Bulk_Image_Alt::get();
		Event_Scheduler::get();
	}

	/**
	 * Add Credits remaining text before the help link in the admin header.
	 */
	public function add_credits_remaining() {
		// Early bail if site is not connected or doesn't have a Content AI Plan.
		if ( ! Helper::is_site_connected() || ! Helper::get_content_ai_plan() ) {
			return;
		}

		$credits = Helper::get_credits();
		?>
		<div class="credits-remaining">
			<?php echo esc_html__( 'Credits Remaining: ', 'rank-math' ); ?>
			<strong><?php echo esc_html( $credits ); ?></strong>
		</div>
		<?php
	}

	/**
	 * Enqueue Content AI Page CSS.
	 */
	public function enqueue() {
		wp_enqueue_style(
			'rank-math-content-ai-page',
			rank_math()->plugin_url() . 'includes/modules/content-ai/assets/css/content-ai-page.css',
			[ 'rank-math-common' ],
			rank_math()->version
		);

		wp_enqueue_script(
			'rank-math-content-ai-page',
			rank_math()->plugin_url() . 'includes/modules/content-ai/assets/js/content-ai-page.js',
			[
				'lodash',
				'wp-dom-ready',
				'rank-math-content-ai',
			],
			rank_math()->version,
			true
		);

		wp_set_script_translations( 'rank-math-content-ai-page', 'rank-math' );
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		$new_label = '<span class="rank-math-new-label" style="color:#ed5e5e;font-size:10px;font-weight:normal;">' . esc_html__( 'New!', 'rank-math' ) . '</span>';

		if ( 'rank-math-content-ai-page' === Param::get( 'page' ) ) {
			Helper::add_json( 'isContentAIPage', true );
			Content_AI::localized_data();
		}

		new Page(
			'rank-math-content-ai-page',
			esc_html__( 'Content AI', 'rank-math' ),
			[
				'position'   => 4,
				'parent'     => 'rank-math',
				// Translators: placeholder is the new label.
				'menu_title' => sprintf( esc_html__( 'Content AI %s', 'rank-math' ), $new_label ),
				'capability' => 'rank_math_content_ai',
				'render'     => dirname( __FILE__ ) . '/views/main.php',
				'classes'    => [ 'rank-math-page' ],
				'assets'     => [
					'styles'  => [
						'wp-edit-post'              => '',
						'rank-math-common'          => '',
						'rank-math-cmb2'            => '',
						'wp-block-library'          => '',
						'rank-math-content-ai-page' => $uri . '/assets/css/content-ai-page.css',
					],
					'scripts' => [
						'lodash'                    => '',
						'wp-components'             => '',
						'wp-block-library'          => '',
						'wp-format-library'         => '',
						'wp-edit-post'              => '',
						'wp-blocks'                 => '',
						'wp-element'                => '',
						'wp-editor'                 => '',
						'rank-math-block-faq'       => rank_math()->plugin_url() . 'assets/admin/js/blocks.js',
						'rank-math-analyzer'        => rank_math()->plugin_url() . 'assets/admin/js/analyzer.js',
						'rank-math-content-ai'      => rank_math()->plugin_url() . 'includes/modules/content-ai/assets/js/content-ai.js',
						'rank-math-content-ai-page' => $uri . '/assets/js/content-ai-page.js',
					],
				],
			]
		);
	}

	/**
	 * Add admin bar item.
	 *
	 * @param Admin_Bar_Menu $menu Menu class instance.
	 */
	public function admin_bar_items( $menu ) {
		$url = Helper::get_admin_url( 'content-ai-page' );
		$menu->add_sub_menu(
			'content-ai-page',
			[
				'title'    => esc_html__( 'Content AI', 'rank-math' ),
				'href'     => $url,
				'priority' => 50,
			]
		);

		$items = [
			'content-ai-tools'   => [
				'title' => esc_html__( 'AI Tools', 'rank-math' ),
				'href'  => $url . '#ai-tools',
				'meta'  => [ 'title' => esc_html__( 'Content AI Tools', 'rank-math' ) ],
			],

			'content-ai-editor'  => [
				'title' => esc_html__( 'Content Editor', 'rank-math' ),
				'href'  => $url . '#content-editor',
				'meta'  => [ 'title' => esc_html__( 'Content AI Editor', 'rank-math' ) ],
			],

			'content-ai-chat'    => [
				'title' => esc_html__( 'Chat', 'rank-math' ),
				'href'  => $url . '#chat',
				'meta'  => [ 'title' => esc_html__( 'Content AI Chat', 'rank-math' ) ],
			],

			'content-ai-history' => [
				'title' => esc_html__( 'History', 'rank-math' ),
				'href'  => $url . '#history',
				'meta'  => [ 'title' => esc_html__( 'Content AI History', 'rank-math' ) ],
			],
		];

		foreach ( $items as $id => $args ) {
			$menu->add_sub_menu( $id, $args, 'content-ai-page' );
		}
	}

	/**
	 * Add Content Editor Settings.
	 */
	public function content_editor_settings() {
		$post                 = $this->get_content_editor_post();
		$block_editor_context = new WP_Block_Editor_Context( [ 'post' => [] ] );

		// Flag that we're loading the block editor.
		$current_screen = get_current_screen();
		$current_screen->is_block_editor( true );

		$editor_settings = [
			'availableTemplates'   => [],
			'disablePostFormats'   => true,
			'autosaveInterval'     => 0,
			'richEditingEnabled'   => user_can_richedit(),
			'supportsLayout'       => function_exists( 'wp_theme_has_theme_json' ) ? wp_theme_has_theme_json() : false,
			'supportsTemplateMode' => false,
			'enableCustomFields'   => false,
		];

		$editor_settings = get_block_editor_settings( $editor_settings, $block_editor_context );

		/**
		 * Scripts
		 */
		wp_enqueue_media( [ 'post' => $post->ID ] );
		?>

		<div id="editor2" data-settings='<?php echo esc_attr( wp_json_encode( $editor_settings ) ); ?>' data-post-id="<?php echo esc_attr( $post->ID ); ?>"></div>
		<?php

		wp_set_script_translations( 'rank-math-content-ai', 'rank-math' );
		wp_set_script_translations( 'rank-math-content-ai-page', 'rank-math' );
	}

	/**
	 * Remove unsed content generated from the Toolbar option of the Content AI.
	 *
	 * @param array $data An array of slashed, sanitized, and processed post data.
	 */
	public function remove_unused_generated_content( $data ) {
		$blocks = parse_blocks( $data['post_content'] );
		if ( empty( $blocks ) ) {
			return $data;
		}

		$update = false;
		foreach ( $blocks as $key => $block ) {
			if ( 'rank-math/command' === $block['blockName'] ) {
				unset( $blocks[ $key ] );
				$update = true;
			}
		}

		if ( $update ) {
			$data['post_content'] = serialize_blocks( $blocks );
		}

		return $data;
	}

	/**
	 * Add database tools.
	 *
	 * @param array $tools Array of tools.
	 *
	 * @return array
	 */
	public function add_tools( $tools ) {
		$posts = get_option( 'rank_math_content_ai_posts' );

		// Early Bail if process is not running.
		if ( empty( $posts ) ) {
			return $tools;
		}

		$processed = get_option( 'rank_math_content_ai_posts_processed' );

		$tools['content_ai_cancel_bulk_edit_process'] = [
			'title'       => esc_html__( 'Cancel Content AI Bulk Editing Process', 'rank-math' ),
			'description' => sprintf(
				// Translators: placeholders are the number of posts that were processed.
				esc_html__( 'Terminate the ongoing Content AI Bulk Editing Process to halt any pending modifications and revert to the previous state. The bulk metadata has been generated for %1$d out of %1$d posts so far.', 'rank-math' ),
				$processed,
				count( $posts )
			),
			'button_text' => esc_html__( 'Terminate', 'rank-math' ),
		];

		return $tools;
	}

	/**
	 * Function to cancel the Bulk Edit process.
	 */
	public function cancel_bulk_edit_process() {
		Bulk_Edit_SEO_Meta::get()->cancel();
		Helper::remove_notification( 'rank_math_content_ai_posts_started' );
		return __( 'Bulk Editing Process Successfully Cancelled', 'rank-math' );
	}

	/**
	 * Register Content AI post type to use the post in Content Editor.
	 */
	private function register_post_type() {
		register_post_type(
			'rm_content_editor',
			[
				'label'               => 'RM Content Editor',
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => false,
				'show_in_rest'        => true,
				'has_archive'         => false,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'delete_with_user'    => false,
				'exclude_from_search' => false,
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => [ 'editor' ],
			]
		);
	}

	/**
	 * Get Content Editor post.
	 *
	 * @return int Post ID.
	 */
	private function get_content_editor_post() {
		$posts = get_posts(
			[
				'post_type'   => 'rm_content_editor',
				'numberposts' => 1,
				'post_status' => 'any',
			]
		);

		if ( empty( $posts ) ) {
			$post_id = wp_insert_post(
				[
					'post_type'    => 'rm_content_editor',
					'post_content' => '<!-- wp:rank-math/command /-->',
				]
			);

			return get_post( $post_id );
		}

		$ai_post = current( $posts );
		$content = $ai_post->post_content;
		$blocks  = parse_blocks( $content );
		if ( ! empty( $blocks ) && count( $blocks ) < 2 && 'core/paragraph' === $blocks[0]['blockName'] ) {
			$content = do_blocks( $ai_post->post_content );
			$content = trim( preg_replace( '/<p[^>]*><\\/p[^>]*>/', '', $content ) );
		}

		if ( ! $content ) {
			wp_update_post(
				[
					'ID'           => $ai_post->ID,
					'post_content' => '<!-- wp:rank-math/command /-->',
				]
			);
		}

		return $ai_post;
	}
}
