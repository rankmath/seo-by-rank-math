<?php
/**
 * Bootstraps the WAP agent personas and their shared configuration.
 *
 * @since      1.0.282
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Agent;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Agent class.
 */
class Agent {

	use Hooker;

	/**
	 * Product slug. Must match a role mapped on the WAP backend.
	 */
	const PRODUCT = 'rankmath';

	/**
	 * WAP backend base URL (wrap-key + chat host).
	 */
	const SERVER_URL = 'https://wordpress-agentic-platform-production.public-default.k8spod4-cph3.ingress.k8s.g1i.one';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		// Support Agent.
		new Support_Agent();
	}

	/**
	 * WAP backend base URL, overridable for staging via a wp-config.php constant.
	 *
	 * SERVER_URL is a raw cluster hostname, so without an override there is no way
	 * to point a site at a non-production backend short of editing this file.
	 *
	 * @return string
	 */
	public static function get_server_url() {
		return defined( 'RANK_MATH_WAP_SERVER_URL' ) ? RANK_MATH_WAP_SERVER_URL : self::SERVER_URL;
	}

	/**
	 * Widget string overrides shared by every Rank Math surface.
	 *
	 * The library's defaults say a neutral "assistant"; these swap in the product
	 * name where it reads as one (header, consent, alerts). Short labels whose
	 * context is clear keep the library's plain "assistant".
	 *
	 * Public so PRO's Content AI embed names the assistant the same way.
	 *
	 * @return array<string, string>
	 */
	public static function get_i18n() {
		return [
			'headerTitle'       => __( 'Rank Math Assistant', 'seo-by-rank-math' ),
			'consentText'       => __( 'Please review and accept the {terms} for the Rank Math Assistant.', 'seo-by-rank-math' ),
			'consentAccessText' => __( 'The Assistant uses a safe {link}WordPress Application Password{/link} with limited permissions, which you can remove at any time.', 'seo-by-rank-math' ),
			'consentRequired'   => __( 'You need to accept the Terms & Conditions to use Rank Math Assistant.', 'seo-by-rank-math' ),
			'errorAuthFailed'   => __( 'Could not connect to Rank Math Assistant. Please reload the page, or contact support if this continues.', 'seo-by-rank-math' ),
			// Only Rank Math abilities can run while "Manage your WordPress site" is locked to deny.
			'applying'          => __( 'Interacting with Rank Math…', 'seo-by-rank-math' ),
		];
	}
}
