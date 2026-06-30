<?php
/**
 * The AI Visibility module.
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\AI_Visibility
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\AI_Visibility;

use RankMath\Traits\Hooker;
use RankMath\AI_Visibility\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * AI_Visibility class.
 */
class AI_Visibility {

	use Hooker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->action( 'rest_api_init', 'init_rest_api' );

		if ( is_admin() ) {
			new Admin();
		}
	}

	/**
	 * Register REST routes for this module.
	 */
	public function init_rest_api() {
		( new Api\Brands_Controller() )->register_routes();
		( new Api\Trial_Controller() )->register_routes();
		( new Api\Checkout_Controller() )->register_routes();
	}
}
