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

use RankMath\KB;
use RankMath\Helper;
use RankMath\Helpers\Arr;
use RankMath\CMB2;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin {
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
		if ( ! is_admin() ) {
			return;
		}

		$this->content_ai = $content_ai;
		$this->filter( 'rank_math/analytics/post_data', 'add_contentai_data', 10, 2 );
		$this->filter( 'rank_math/settings/general', 'add_settings' );
		$this->action( 'cmb2_admin_init', 'add_content_ai_metabox', 11 );
		$this->action( 'rank_math/deregister_site', 'remove_credits_data' );
		$this->filter( 'rank_math/status/rank_math_info', 'content_ai_info' );
		$this->action( 'rank_math/connect/account_connected', 'refresh_content_ai_credits' );
	}

	/**
	 * Add Content AI score in Single Page Site Analytics.
	 *
	 * @param  array            $data array.
	 * @param  \WP_REST_Request $request post object.
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
					'icon'  => 'rm-icon rm-icon-content-ai',
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
		if ( ! $this->content_ai->can_add_tab() || 'classic' !== Helper::get_current_editor() ) {
			return;
		}

		$id  = 'rank_math_metabox_content_ai';
		$cmb = new_cmb2_box(
			[
				'id'               => $id,
				'title'            => esc_html__( 'Content AI', 'rank-math' ),
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
	 * Add Content AI details in System Info
	 *
	 * @param array $rankmath Array of rankmath.
	 */
	public function content_ai_info( $rankmath ) {
		$refresh_date = Helper::get_content_ai_refresh_date();
		$content_ai   = [
			'ca_plan'         => [
				'label' => esc_html__( 'Content AI Plan', 'rank-math' ),
				'value' => \ucwords( Helper::get_content_ai_plan() ),
			],
			'ca_credits'      => [
				'label' => esc_html__( 'Content AI Credits', 'rank-math' ),
				'value' => Helper::get_content_ai_credits(),
			],
			'ca_refresh_date' => [
				'label' => esc_html__( 'Content AI Refresh Date', 'rank-math' ),
				'value' => $refresh_date ? wp_date( 'Y-m-d g:i a', $refresh_date ) : '',
			],
		];

		array_splice( $rankmath['fields'], 3, 0, $content_ai );

		return $rankmath;
	}

	/**
	 * Refresh Content AI credits when account is connected.
	 *
	 * @param array $data Authentication data.
	 *
	 * @return void
	 */
	public function refresh_content_ai_credits( $data ) {
		Helper::get_content_ai_credits( true );
	}

	/**
	 * Reorder the Content AI metabox in Classic editor.
	 *
	 * @param string $id Metabox ID.
	 * @return void
	 */
	private function reorder_content_ai_metabox( $id ) {
		$post_type = Helper::get_post_type();
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
}
