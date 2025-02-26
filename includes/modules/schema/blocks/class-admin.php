<?php
/**
 * The Block Admin
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema\Blocks;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Arr;

defined( 'ABSPATH' ) || exit;

/**
 * Block Admin class.
 */
class Admin {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->filter( 'rank_math/settings/general', 'add_general_settings' );
	}

	/**
	 * Add block settings into general optional panel.
	 *
	 * @param array $tabs Array of option panel tabs.
	 *
	 * @return array
	 */
	public function add_general_settings( $tabs ) {
		Arr::insert(
			$tabs,
			[
				'blocks' => [
					'icon'  => 'rm-icon rm-icon-stories',
					'title' => esc_html__( 'Blocks', 'rank-math' ),
					'desc'  => esc_html__( 'Take control over the default settings available for Rank Math Blocks.', 'rank-math' ),
					'file'  => __DIR__ . '/views/options-general.php',
				],
			],
			7
		);

		return $tabs;
	}
}
