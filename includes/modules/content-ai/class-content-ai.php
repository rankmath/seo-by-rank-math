<?php
/**
 * The Content AI module.
 *
 * @since      1.0.71
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ContentAI;

use RankMath\KB;
use RankMath\Helper;
use RankMath\CMB2;
use RankMath\Traits\Hooker;
use RankMath\Traits\Ajax;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Content_AI class.
 */
class Content_AI {
	use Hooker, Ajax;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'rest_api_init', 'init_rest_api' );

		if ( ! Helper::has_cap( 'content_ai' ) ) {
			return;
		}

		$this->filter( 'rank_math/analytics/post_data', 'add_contentai_data', 10, 2 );
		$this->filter( 'rank_math/settings/general', 'add_settings' );
		$this->action( 'rank_math/admin/editor_scripts', 'editor_scripts', 20 );
		$this->filter( 'rank_math/metabox/post/values', 'add_metadata', 10, 2 );
		$this->action( 'cmb2_admin_init', 'add_content_ai_metabox', 11 );
		$this->action( 'rank_math/deregister_site', 'remove_credits_data' );
		$this->ajax( 'get_content_ai_credits', 'update_content_ai_credits' );
		$this->filter( 'rank_math/elementor/dark_styles', 'add_dark_style' );
	}

	/**
	 * Add dark style
	 *
	 * @param array $styles The dark mode styles.
	 */
	public function add_dark_style( $styles = [] ) {

		$styles['rank-math-content-ai-dark'] = rank_math()->plugin_url() . 'includes/modules/content-ai/assets/css/content-ai-dark.css';

		return $styles;
	}

	/**
	 * Add Content AI score in Single Page Site Analytics.
	 *
	 * @param  array           $data array.
	 * @param  WP_REST_Request $request post object.
	 * @return array $data sorted array.
	 */
	public function add_contentai_data( $data, \WP_REST_Request $request ) {
		$post_id                = $data['object_id'];
		$content_ai_data        = Helper::get_post_meta( 'contentai_score', $post_id );
		$content_ai_score       = ! empty( $content_ai_data ) ? round( array_sum( array_values( $content_ai_data ) ) / count( $content_ai_data ) ) : 0;
		$data['contentAiScore'] = absint( $content_ai_score );

		return $data;
	}

	/**
	 * Load the REST API endpoints.
	 */
	public function init_rest_api() {
		$rest = new Rest();
		$rest->register_routes();
	}

	/**
	 * Remove credits data when site is disconnected.
	 */
	public function remove_credits_data() {
		delete_option( 'rank_math_ca_credits' );
	}

	/**
	 * Add module settings in the General Settings panel.
	 *
	 * @param  array $tabs Array of option panel tabs.
	 * @return array
	 */
	public function add_settings( $tabs ) {
		Arr::insert(
			$tabs,
			[
				'content-ai' => [
					'icon'  => 'rm-icon rm-icon-target',
					'title' => esc_html__( 'Content AI', 'rank-math' ),
					/* translators: Link to kb article */
					'desc'  => sprintf( esc_html__( 'Get sophisticated AI suggestions for related Keywords, Questions & Links to include in the SEO meta & Content Area. %s.', 'rank-math' ), '<a href="' . KB::get( 'content-ai-settings', 'Options Panel Content AI Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
					'file'  => dirname( __FILE__ ) . '/views/options.php',
				],
			],
			8
		);

		return $tabs;
	}

	/**
	 * Add link suggestion metabox.
	 */
	public function add_content_ai_metabox() {
		if ( ! self::can_add_tab() || 'classic' !== Helper::get_current_editor() ) {
			return;
		}

		$id  = 'rank_math_metabox_content_ai';
		$cmb = new_cmb2_box(
			[
				'id'               => $id,
				'title'            => esc_html__( 'Rank Math Content AI', 'rank-math' ),
				'object_types'     => array_keys( Helper::get_accessible_post_types() ),
				'context'          => 'side',
				'priority'         => 'high',
				'mb_callback_args' => [ '__block_editor_compatible_meta_box' => false ],
			]
		);

		CMB2::pre_init( $cmb );

		// Move content AI metabox below the Publish box.
		$this->reorder_content_ai_metabox( $id );
	}


	/**
	 * Enqueue assets for post/term/user editors.
	 *
	 * @return void
	 */
	public function editor_scripts() {
		if ( ! self::can_add_tab() ) {
			return;
		}

		$editor = Helper::get_current_editor();
		if ( ! $editor ) {
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
	}

	/**
	 * Add meta data to use in gutenberg.
	 *
	 * @param array  $values Aray of tabs.
	 * @param Screen $screen Sceen object.
	 *
	 * @return array
	 */
	public function add_metadata( $values, $screen ) {
		$countries = [];
		foreach ( Helper::choices_contentai_countries() as $value => $label ) {
			$countries[] = [
				'value' => $value,
				'label' => $label,
			];
		}

		$values['contentAiCountry'] = Helper::get_settings( 'general.content_ai_country', 'all' );
		$values['countries']        = $countries;
		$values['ca_credits']       = get_option( 'rank_math_ca_credits' );
		$values['ca_keyword']       = '';
		$values['ca_viewed']        = true;

		$content_ai_viewed = get_option( 'rank_math_content_ai_viewed', false );
		if ( ! $content_ai_viewed ) {
			$values['ca_viewed'] = false;
			update_option( 'rank_math_content_ai_viewed', true );
		}
		$keyword = $screen->get_meta( $screen->get_object_type(), $screen->get_object_id(), 'rank_math_ca_keyword' );
		if ( empty( $keyword ) ) {
			return $values;
		}

		$data    = get_option( 'rank_math_ca_data' );
		$country = empty( $keyword['country'] ) ? '' : $keyword['country'];
		if (
			! empty( $data[ $country ] ) &&
			! empty( $data[ $country ][ mb_strtolower( $keyword['keyword'] ) ] )
		) {
			$values['ca_data'] = $data[ $country ][ mb_strtolower( $keyword['keyword'] ) ];
		}

		$values['ca_keyword'] = $keyword;

		$content_ai_data          = $screen->get_meta( $screen->get_object_type(), $screen->get_object_id(), 'rank_math_contentai_score' );
		$content_ai_score         = ! empty( $content_ai_data ) && is_array( $content_ai_data ) ? round( array_sum( array_values( $content_ai_data ) ) / count( $content_ai_data ) ) : 0;
		$values['contentAiScore'] = absint( $content_ai_score );

		return $values;
	}

	/**
	 * Ajax callback to update the Content AI Credits.
	 */
	public function update_content_ai_credits() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'content_ai' );
		$this->success(
			[
				'credits' => Helper::get_content_ai_credits( true ),
			]
		);
	}

	/**
	 * Reorder the Content AI metabox in Classic editor.
	 *
	 * @param string $id Metabox ID.
	 * @return void
	 */
	private function reorder_content_ai_metabox( $id ) {
		$post_type = WordPress::get_post_type();
		if ( ! $post_type ) {
			return;
		}

		$user  = wp_get_current_user();
		$order = (array) get_user_option( 'meta-box-order_' . $post_type, $user->ID );
		if ( ! empty( $order['normal'] ) && false !== strpos( $order['normal'], $id ) ) {
			return;
		}

		$order['side'] = ! isset( $order['side'] ) ? '' : $order['side'];
		if ( false !== strpos( $order['side'], $id ) ) {
			return;
		}

		if ( false === strpos( $order['side'], 'submitdiv' ) ) {
			$order['side'] = 'submitdiv,' . $order['side'];
		}

		if ( ',' === substr( $order['side'], -1 ) ) {
			$order['side'] = substr( $order['side'], 0, -1 );
		}

		$current_order = [];
		$current_order = explode( ',', $order['side'] );

		$key = array_search( 'submitdiv', $current_order, true );
		if ( false === $key ) {
			return;
		}

		$new_order = array_merge(
			array_slice( $current_order, 0, $key + 1 ),
			[ $id ]
		);

		if ( count( $current_order ) > $key ) {
			$new_order = array_merge(
				$new_order,
				array_slice( $current_order, $key + 1 )
			);
		}

		$order['side'] = implode( ',', array_unique( $new_order ) );
		update_user_option( $user->ID, 'meta-box-order_' . $post_type, $order, true );
	}

	/**
	 * Whether to load Content AI data.
	 */
	public static function can_add_tab() {
		return in_array( WordPress::get_post_type(), (array) Helper::get_settings( 'general.content_ai_post_types' ), true );
	}
}
