<?php
/**
 * Registers the "Agent (Beta)" chat page via the WAP client library.
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Agent;

use RankMath\KB;
use RankMath\Helpers\Param;
use RankMath\Traits\Hooker;
use RankMath\Traits\Grnd_Provider;

defined( 'ABSPATH' ) || exit;

/**
 * Agent class.
 */
class Agent {

	use Hooker;
	use Grnd_Provider;

	/**
	 * Product slug. Must match a role mapped on the WAP backend.
	 */
	const PRODUCT = 'rankmath';

	/**
	 * WAP backend base URL.
	 */
	const SERVER_URL = 'https://wordpress-agentic-platform-production.public-default.k8spod4-cph3.ingress.k8s.g1i.one';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->filter( 'wap_client_capability', 'filter_capability' );

		// Support Agent.
		new Support_Agent();
	}

	/**
	 * Gate the WAP widget on manage_options instead of the library's own wap_use_ai.
	 *
	 * @return string
	 */
	public function filter_capability() {
		return 'manage_options';
	}

	/**
	 * GRND provider callback: mints, seals, and exchanges the credential for a GRND.
	 *
	 * @return array{grnd:string,expires_at:int}|\WP_Error
	 */
	public function acquire_grnd() {
		return $this->acquire_grnd_for( self::PRODUCT, 'Rank Math AI Assistant' );
	}
}
