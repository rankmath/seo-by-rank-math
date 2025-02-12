<?php
/**
 * Members plugin integration.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Role_Manager
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Role_Manager;

use RankMath\Traits\Hooker;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Members class.
 */
class Members {

	use Hooker;

	/**
	 * Members cap group name.
	 *
	 * @var string
	 */
	const GROUP = 'rank_math';

	/**
	 * Class Members constructor.
	 */
	public function __construct() {
		$this->action( 'members_register_caps', 'register_caps' );
		$this->action( 'members_register_cap_groups', 'register_cap_groups' );
	}

	/**
	 * Registers cap group.
	 */
	public function register_cap_groups() {
		// @phpstan-ignore-next-line
		\members_register_cap_group(
			self::GROUP,
			[
				'label'    => esc_html__( 'Rank Math', 'rank-math' ),
				'caps'     => [],
				'icon'     => 'dashicons-chart-area',
				'priority' => 30,
			]
		);
	}

	/**
	 * Registers caps.
	 */
	public function register_caps() {
		$caps = Capability_Manager::get()->get_capabilities();
		if ( 'administrator' === Param::get( 'role' ) ) {
			$caps['rank_math_edit_htaccess'] = esc_html__( 'Edit .htaccess', 'rank-math' );
		}

		foreach ( $caps as $key => $value ) {
			// @phpstan-ignore-next-line
			\members_register_cap(
				$key,
				[
					'label' => html_entity_decode( $value ),
					'group' => self::GROUP,
				]
			);
		}
	}
}
