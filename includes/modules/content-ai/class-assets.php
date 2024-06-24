<?php
/**
 * The Content AI module.
 *
 * @since      1.0.219
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ContentAI;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Assets {
	use Hooker;

	/**
	 * Content_AI object.
	 *
	 * @var object
	 */
	public $content_ai;

	/**
	 * Class constructor.
	 *
	 * @param Object $content_ai Content_AI class object.
	 */
	public function __construct( $content_ai ) {
		$this->content_ai = $content_ai;
		$this->action( 'rank_math/admin/editor_scripts', 'editor_scripts', 20 );
		$this->filter( 'rank_math/elementor/dark_styles', 'add_dark_style' );
		$this->action( 'admin_enqueue_scripts', 'media_scripts', 20 );
	}

	/**
	 * Add dark style.
	 *
	 * @param array $styles The dark mode styles.
	 */
	public function add_dark_style( $styles = [] ) {

		$styles['rank-math-content-ai-dark'] = rank_math()->plugin_url() . 'includes/modules/content-ai/assets/css/content-ai-dark.css';

		return $styles;
	}

	/**
	 * Enqueue Content AI files in the enabled post types.
	 *
	 * @param WP_Screen $screen Post screen object.
	 *
	 * @return void
	 */
	public function editor_scripts( $screen ) {
		if ( ! $this->content_ai->can_add_tab() || ! Helper::get_current_editor() ) {
			return;
		}

		wp_register_style( 'rank-math-common', rank_math()->plugin_url() . 'assets/admin/css/common.css', null, rank_math()->version );
		wp_enqueue_style(
			'rank-math-content-ai',
			rank_math()->plugin_url() . 'includes/modules/content-ai/assets/css/content-ai.css',
			[ 'rank-math-common' ],
			rank_math()->version
		);

		wp_enqueue_script(
			'rank-math-content-ai',
			rank_math()->plugin_url() . 'includes/modules/content-ai/assets/js/content-ai.js',
			[ 'rank-math-editor' ],
			rank_math()->version,
			true
		);

		wp_enqueue_style(
			'rank-math-content-ai-page',
			rank_math()->plugin_url() . 'includes/modules/content-ai/assets/css/content-ai-page.css',
			[ 'rank-math-common' ],
			rank_math()->version
		);
		wp_set_script_translations( 'rank-math-content-ai', 'rank-math' );

		$this->content_ai->localized_data( $this->get_post_localized_data( $screen ) );
	}

	/**
	 * Enqueue our inject-generate-alt-text script on the Edit Media page (post.php with post_type=attachment).
	 */
	public function media_scripts() {
		$screen = \function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( ! $screen || 'attachment' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			'rank-math-content-ai-media',
			rank_math()->plugin_url() . 'includes/modules/content-ai/assets/js/content-ai-media.js',
			[ 'jquery', 'wp-api-fetch', 'lodash', 'wp-element', 'wp-components' ],
			rank_math()->version,
			true
		);

		wp_enqueue_style(
			'rank-math-content-ai-page',
			rank_math()->plugin_url() . 'includes/modules/content-ai/assets/css/content-ai-page.css',
			[ 'rank-math-common', 'wp-components' ],
			rank_math()->version
		);

		$this->content_ai->localized_data();
	}

	/**
	 * Add meta data to use in gutenberg.
	 *
	 * @param Screen $screen Sceen object.
	 *
	 * @return array
	 */
	private function get_post_localized_data( $screen ) {
		$values    = [];
		$countries = [];
		foreach ( Helper::choices_contentai_countries() as $value => $label ) {
			$countries[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		$values = [
			'country'   => Helper::get_settings( 'general.content_ai_country', 'all' ),
			'countries' => $countries,
			'viewed'    => true,
			'keyword'   => '',
			'score'     => [
				'keywords'     => 0,
				'wordCount'    => 0,
				'linkCount'    => 0,
				'headingCount' => 0,
				'mediaCount'   => 0,
			],
		];

		$content_ai_viewed = get_option( 'rank_math_content_ai_viewed', false );
		if ( ! $content_ai_viewed ) {
			$values['viewed'] = false;
			update_option( 'rank_math_content_ai_viewed', true );
		}

		$researched_values = $screen->get_meta( $screen->get_object_type(), $screen->get_object_id(), 'rank_math_ca_keyword' );
		if ( empty( $researched_values ) ) {
			return $values;
		}

		$data    = get_option( 'rank_math_ca_data' );
		$keyword = empty( $researched_values['keyword'] ) ? '' : $researched_values['keyword'];
		$country = empty( $researched_values['country'] ) ? '' : $researched_values['country'];
		if (
			! empty( $data[ $country ] ) &&
			! empty( $data[ $country ][ mb_strtolower( $keyword ) ] )
		) {
			$values['researchedData'] = $data[ $country ][ mb_strtolower( $keyword ) ];
		}

		$values['keyword'] = $keyword;
		$values['country'] = $country;
		$content_ai_data   = $screen->get_meta( $screen->get_object_type(), $screen->get_object_id(), 'rank_math_contentai_score' );
		$values['score']   = (array) $content_ai_data;

		return $values;
	}
}
