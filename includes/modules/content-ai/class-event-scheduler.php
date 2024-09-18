<?php
/**
 * The Event Scheduler for Content AI to update the prompts and credits data.
 *
 * @since      1.0.123
 * @package    RankMath
 * @subpackage RankMath\ContentAI
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ContentAI;

use WP_Block_Type_Registry;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Event_Scheduler class.
 */
class Event_Scheduler {

	use Hooker;

	/**
	 * The single instance of the class.
	 *
	 * @var Event_Scheduler
	 */
	protected static $instance = null;

	/**
	 * Retrieve main Block_Command instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Event_Scheduler
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof Event_Scheduler ) ) {
			self::$instance = new Event_Scheduler();
		}

		return self::$instance;
	}

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( ! Helper::is_site_connected() ) {
			return;
		}

		$credits = get_option( 'rank_math_ca_credits' );
		if ( ! empty( $credits['refresh_date'] ) ) {
			wp_schedule_single_event( absint( $credits['refresh_date'] ) + 60, 'rank_math/content-ai/update_plan' );
		}

		$this->action( 'rank_math/content-ai/update_prompts', 'update_prompts_data' );
		$this->action( 'rank_math/content-ai/update_plan', 'update_content_ai_plan' );
		$this->action( 'admin_footer', 'update_prompts_on_new_site' );
	}

	/**
	 * Fetch and update the prompts data daily.
	 *
	 * @return void
	 */
	public function update_prompts_data() {
		if ( ! Helper::get_credits() || ! Helper::get_content_ai_plan() ) {
			return;
		}

		$registered = Admin_Helper::get_registration_data();
		if ( empty( $registered ) || empty( $registered['username'] ) ) {
			return;
		}

		$prompt_data   = [];
		$data          = wp_remote_post(
			CONTENT_AI_URL . '/ai/default_prompts',
			[
				'headers' => [
					'Content-type' => 'application/json',
				],
				'body'    => wp_json_encode(
					[
						'username' => $registered['username'],
						'api_key'  => $registered['api_key'],
						'site_url' => $registered['site_url'],
					]
				),
			]
		);
		$response_code = wp_remote_retrieve_response_code( $data );
		if ( is_wp_error( $data ) || ! in_array( $response_code, [ 200, 201 ], true ) ) {
			return;
		}

		update_option( 'rank_math_prompts_updated', true );
		$data = wp_remote_retrieve_body( $data );
		$data = json_decode( $data, true );
		if ( empty( $data ) ) {
			return;
		}

		Helper::save_default_prompts( $data );
	}

	/**
	 * Run the credits endpoint to update the plan on reset date.
	 *
	 * @return void
	 */
	public function update_content_ai_plan() {
		Helper::get_content_ai_credits( true );
		wp_clear_scheduled_hook( 'rank_math/content-ai/update_plan' );
	}

	/**
	 * Function to update Prompts data on new sites.
	 *
	 * @return void
	 */
	public function update_prompts_on_new_site() {
		$prompts = Helper::get_prompts();
		if ( get_option( 'rank_math_prompts_updated' ) || ! empty( $prompts ) ) {
			return;
		}

		do_action( 'rank_math/content-ai/update_prompts' );
	}
}
