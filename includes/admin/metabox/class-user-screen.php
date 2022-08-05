<?php
/**
 * The metabox functionality of the plugin.
 *
 * @since      1.0.25
 * @package    RankMath
 * @subpackage RankMath\Admin\Metabox
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Metabox;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * User metabox class.
 */
class User_Screen implements IScreen {

	use Hooker;

	/**
	 * Class construct
	 */
	public function __construct() {}

	/**
	 * Get object ID.
	 *
	 * @return int
	 */
	public function get_object_id() {
		global $user_id;

		return $user_id;
	}

	/**
	 * Get object type
	 *
	 * @return string
	 */
	public function get_object_type() {
		return 'user';
	}

	/**
	 * Get object types to register metabox to
	 *
	 * @return array
	 */
	public function get_object_types() {
		return [ 'user' ];
	}

	/**
	 * Enqueue Styles and Scripts required for screen.
	 */
	public function enqueue() {
		wp_enqueue_media();
	}

	/**
	 * Get analysis to run.
	 *
	 * @return array
	 */
	public function get_analysis() {
		return [
			'keywordInTitle'           => true,
			'keywordInMetaDescription' => true,
			'keywordInPermalink'       => true,
			'keywordNotUsed'           => true,
			'titleStartWithKeyword'    => true,
		];
	}

	/**
	 * Get values for localize.
	 *
	 * @return array
	 */
	public function get_values() {
		global $wp_rewrite;
		return [
			'permalinkFormat' => ! empty( $wp_rewrite->author_structure ) ? home_url( $wp_rewrite->author_structure ) : home_url(),
		];
	}

	/**
	 * Get object values for localize
	 *
	 * @return array
	 */
	public function get_object_values() {
		return [
			'titleTemplate'       => Helper::get_settings( 'titles.author_archive_title', '%name% %sep% %sitename% %page%' ),
			'descriptionTemplate' => Helper::get_settings( 'titles.author_archive_description', '%user_description%' ),
		];
	}

	/**
	 * Is user metabox enabled.
	 *
	 * @return bool
	 */
	public static function is_enable() {
		return false === Helper::get_settings( 'titles.disable_author_archives' ) &&
			Helper::get_settings( 'titles.author_add_meta_box' ) &&
			Admin_Helper::is_user_edit();
	}
}
