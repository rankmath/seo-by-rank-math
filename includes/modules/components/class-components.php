<?php
/**
 * The Components UI.
 *
 * @since      1.0.71
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Components;

use RankMath\Traits\Hooker;
use MyThemeShop\Admin\Page;

defined( 'ABSPATH' ) || exit;

/**
 * Content_AI class.
 */
class WP_Components {
	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'init', 'init' );
	}

	/**
	 * Init function.
	 */
	public function init() {
		$this->register_admin_page();
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		new Page(
			'rank-math-components-ui',
			esc_html__( 'Components UI', 'rank-math' ),
			[
				'position'   => 4,
				'parent'     => 'rank-math',
				'capability' => 'rank_math_content_ai',
				'render'     => dirname( __FILE__ ) . '/views/main.php',
				'classes'    => [ 'rank-math-page' ],
				'assets'     => [
					'styles'  => [
						'rank-math-components-ui' => $uri . '/assets/css/components.css',
					],
					'scripts' => [
						'wp-components'             => '',
						'rank-math-components-ui'  => rank_math()->plugin_url() . 'includes/modules/components/assets/js/components.js',
					],
				],
			]
		);
	}
}
