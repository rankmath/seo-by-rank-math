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

use RankMath\Traits\Hooker;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Helpers\Url;

defined( 'ABSPATH' ) || exit;

/**
 * Content_AI class.
 */
class Content_AI {
	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'rest_api_init', 'init_rest_api' );
		new Content_AI_Page( $this );
		new Bulk_Actions();

		if ( ! Helper::has_cap( 'content_ai' ) ) {
			return;
		}

		new Admin( $this );
		new Assets( $this );
	}

	/**
	 * Load the REST API endpoints.
	 */
	public function init_rest_api() {
		$rest = new Rest();
		$rest->register_routes();
	}

	/**
	 * Whether to load Content AI data.
	 */
	public static function can_add_tab() {
		return in_array( Helper::get_post_type(), (array) Helper::get_settings( 'general.content_ai_post_types' ), true );
	}

	/**
	 * Localized data to use on the Content AI page.
	 *
	 * @param array $data Localized data for posts.
	 */
	public function localized_data( $data = [] ) {
		$refresh_date = Helper::get_content_ai_refresh_date();
		Helper::add_json(
			'contentAI',
			array_merge(
				$data,
				[
					'audience'              => (array) Helper::get_settings( 'general.content_ai_audience', 'General Audience' ),
					'tone'                  => (array) Helper::get_settings( 'general.content_ai_tone', 'Formal' ),
					'language'              => Helper::get_settings( 'general.content_ai_language', Helper::content_ai_default_language() ),
					'history'               => Helper::get_outputs(),
					'chats'                 => Helper::get_chats(),
					'recentPrompts'         => Helper::get_recent_prompts(),
					'prompts'               => Helper::get_prompts(),
					'isUserRegistered'      => Helper::is_site_connected(),
					'connectData'           => Admin_Helper::get_registration_data(),
					'connectSiteUrl'        => Admin_Helper::get_activate_url( Url::get_current_url() ),
					'credits'               => Helper::get_content_ai_credits(),
					'plan'                  => Helper::get_content_ai_plan(),
					'errors'                => Helper::get_content_ai_errors(),
					'registerWriteShortcut' => version_compare( get_bloginfo( 'version' ), '6.2', '>=' ),
					'isMigrating'           => get_site_transient( 'rank_math_content_ai_migrating_user' ),
					'url'                   => CONTENT_AI_URL . '/ai/',
					'resetDate'             => $refresh_date ? wp_date( 'Y-m-d g:ia', $refresh_date ) : '',
				]
			)
		);
	}
}
