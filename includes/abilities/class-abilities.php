<?php
/**
 * WordPress Abilities API integration for Rank Math SEO.
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities;

use RankMath\Abilities\SEO_Analysis\Subscriber as SEO_Analysis_Subscriber;

defined( 'ABSPATH' ) || exit;

/**
 * Bootstraps Rank Math feature subscribers with the WordPress Abilities API.
 */
class Abilities {

	/**
	 * Singleton instance.
	 *
	 * @var Abilities
	 */
	private static $instance;

	/**
	 * Get singleton.
	 *
	 * @return Abilities
	 */
	public static function get() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — registers feature subscribers if the Abilities API is available.
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$this->register_subscribers();
	}

	/**
	 * Instantiate and register all feature subscribers.
	 *
	 * @return void
	 */
	private function register_subscribers() {
		$shared_meta = $this->shared_meta();

		$subscribers = [
			new SEO_Analysis_Subscriber( $shared_meta ),
		];

		foreach ( $subscribers as $subscriber ) {
			$subscriber->register();
		}
	}

	/**
	 * Default meta args shared by every ability we register.
	 *
	 * @return array
	 */
	private function shared_meta() {
		return [
			'show_in_rest' => true,
			'annotations'  => [
				'readonly'    => false,
				'destructive' => false,
				'idempotent'  => true,
			],
			'mcp'          => [ 'public' => true ],
		];
	}
}
