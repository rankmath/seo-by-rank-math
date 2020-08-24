<?php
/**
 * The BuddyPress Module
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
 * BuddyPress class.
 */
class BuddyPress {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			new Admin();
		}

		$this->filter( 'rank_math/paper/hash', 'paper' );
		$this->action( 'rank_math/vars/register_extra_replacements', 'register_replacements' );
		$this->filter( 'rank_math/json_ld', 'json_ld', 11, 2 );
	}

	/**
	 * Add BuddyPress class.
	 *
	 * @param array $hash Paper Hash.
	 */
	public function paper( $hash ) {
		$bp_data = [
			'BP_User'  => bp_is_user(),
			'BP_Group' => ! is_singular() && bp_is_groups_component(),
		];

		return array_merge( $bp_data, $hash );
	}

	/**
	 * Collect data to output in JSON-LD.
	 *
	 * @param array  $data An array of data to output in JSON-LD.
	 * @param JsonLD $json JsonLD instance.
	 */
	public function json_ld( $data, $json ) {
		if ( ! bp_is_user() ) {
			return $data;
		}

		if ( isset( $data['richSnippet'] ) ) {
			unset( $data['richSnippet'] );
		}

		$user_id = bp_displayed_user_id();

		$data['ProfilePage'] = [
			'@type'    => 'ProfilePage',
			'headline' => sprintf( 'About %s', get_the_author_meta( 'display_name', $user_id ) ),
			'about'    => [
				'@type'       => 'Person',
				'name'        => get_the_author_meta( 'display_name', $user_id ),
				'url'         => esc_url( bp_core_get_user_domain( $user_id ) ),
				'description' => get_the_author_meta( 'description', $user_id ),
				'image'       => [
					'@type'  => 'ImageObject',
					'url'    => get_avatar_url( $user_id, 96 ),
					'height' => 96,
					'width'  => 96,
				],
			],
		];
		return $data;
	}

	/**
	 * Registers variable replacements for WooCommerce products.
	 */
	public function register_replacements() {
		rank_math_register_var_replacement(
			'group_name',
			[
				'name'        => esc_html__( 'Group name.', 'rank-math' ),
				'description' => esc_html__( 'Group name of the current group', 'rank-math' ),
				'variable'    => 'group_name',
				'example'     => $this->get_group_name(),
			],
			[ $this, 'get_group_name' ]
		);

		rank_math_register_var_replacement(
			'group_desc',
			[
				'name'        => esc_html__( 'Group Description.', 'rank-math' ),
				'description' => esc_html__( 'Group description of the current group', 'rank-math' ),
				'variable'    => 'group_desc',
				'example'     => $this->get_group_desc(),
			],
			[ $this, 'get_group_desc' ]
		);
	}

	/**
	 * Retrieves the group name.
	 *
	 * @return string
	 */
	public function get_group_name() {
		$group = $this->get_group();
		if ( ! is_object( $group ) ) {
			return '';
		}

		return $group->name;
	}

	/**
	 * Retrieves the group description.
	 *
	 * @return string
	 */
	public function get_group_desc() {
		$group = $this->get_group();
		if ( ! is_object( $group ) ) {
			return '';
		}

		return $group->description;
	}

	/**
	 * Returns the group object when the current page is the group page.
	 *
	 * @return null|Object
	 */
	private function get_group() {

		if ( ! function_exists( 'groups_get_current_group' ) ) {
			return '';
		}

		$group = groups_get_current_group();
		if ( ! is_object( $group ) ) {
			return '';
		}

		return $group;
	}
}
