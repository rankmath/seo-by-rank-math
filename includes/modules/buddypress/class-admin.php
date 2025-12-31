<?php
/**
 * The admin-side code for the BuddyPress module.
 *
 * @since      1.0.32
 * @package    RankMath
 * @subpackage RankMath\BuddyPress
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\BuddyPress;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class
 */
class Admin {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->filter( 'rank_math/settings/title', 'add_title_settings' );
	}

	/**
	 * Add new tab in the Titles & Meta settings for the BuddyPress module.
	 *
	 * @param array $tabs Array of option panel tabs.
	 *
	 * @return array
	 */
	public function add_title_settings( $tabs ) {
		$tabs['buddypress'] = [
			'title' => esc_html__( 'BuddyPress:', 'rank-math' ),
			'type'  => 'separator',
		];

		$tabs['buddypress-groups'] = [
			'icon'  => 'rm-icon rm-icon-users',
			'title' => esc_html__( 'Groups', 'rank-math' ),
			'desc'  => esc_html__( 'This tab contains SEO options for BuddyPress Group pages.', 'rank-math' ),
			'file'  => __DIR__ . '/views/options-titles.php',
		];

		return $tabs;
	}
}
