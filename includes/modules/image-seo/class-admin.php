<?php
/**
 * The admin-side functionality of the Image SEO module.
 *
 * @since      1.0
 * @package    RankMath
 * @subpackage RankMath\Image_Seo
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Image_Seo;

use RankMath\KB;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Arr;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin {

	use Hooker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/settings/general', 'register_tab' );
	}

	/**
	 * Add the Images tab in the General Settings.
	 *
	 * @param  array $tabs Original tabs array.
	 * @return array       New tabs array.
	 */
	public function register_tab( $tabs ) {
		Arr::insert(
			$tabs,
			[
				'images' => [
					'icon'  => 'rm-icon rm-icon-images',
					'title' => esc_html__( 'Images', 'rank-math' ),
					/* translators: Link to kb article */
					'desc'  => sprintf( esc_html__( 'SEO options related to featured images and media appearing in your post content. %s.', 'rank-math' ), '<a href="' . KB::get( 'image-settings', 'Options Panel Images Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
					'file'  => dirname( __FILE__ ) . '/options.php',
				],
			],
			3
		);

		return $tabs;
	}

}
