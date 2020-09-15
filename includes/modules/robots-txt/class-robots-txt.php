<?php
/**
 * The robots txt module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Robots_Txt class.
 */
class Robots_Txt {

	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		if ( is_super_admin() ) {
			$this->filter( 'rank_math/settings/general', 'add_settings' );
		}

		// Custom robots text.
		if ( ! is_admin() && Helper::get_settings( 'general.robots_txt_content' ) ) {
			$this->action( 'robots_txt', 'robots_txt', 10, 2 );
		}
	}

	/**
	 * Replace robots.txt content.
	 *
	 * @param string $content Robots.txt file content.
	 * @param bool   $public  Whether the site is considered "public".
	 *
	 * @return string New robots.txt content.
	 */
	public function robots_txt( $content, $public ) {
		return 0 === absint( $public ) ? $content : Helper::get_settings( 'general.robots_txt_content' );
	}

	/**
	 * Add module settings into general optional panel.
	 *
	 * @param array $tabs Array of option panel tabs.
	 *
	 * @return array
	 */
	public function add_settings( $tabs ) {
		Arr::insert(
			$tabs,
			[
				'robots' => [
					'icon'      => 'rm-icon rm-icon-robots',
					'title'     => esc_html__( 'Edit robots.txt', 'rank-math' ),
					/* translators: Link to kb article */
					'desc'      => sprintf( esc_html__( 'Edit your robots.txt file to control what bots see. %s.', 'rank-math' ), '<a href="' . KB::get( 'edit-robotstxt' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
					'file'      => dirname( __FILE__ ) . '/options.php',
					'classes'   => 'rank-math-advanced-option',
					'after_row' => '<div class="rank-math-desc">' . __( 'Leave the field empty to let WordPress handle the contents dynamically. If an actual robots.txt file is present in the root folder of your site, this option won\'t take effect and you have to edit the file directly, or delete it and then edit from here.', 'rank-math' ) . '</div>',
				],
			],
			5
		);

		return $tabs;
	}

	/**
	 * Get robots.txt related data.
	 *
	 * @return array
	 */
	public static function get_robots_data() {
		$wp_filesystem = WordPress::get_filesystem();
		$public        = absint( get_option( 'blog_public' ) );

		if ( $wp_filesystem->exists( ABSPATH . 'robots.txt' ) ) {
			return [
				'exists'  => true,
				'default' => $wp_filesystem->get_contents( ABSPATH . 'robots.txt' ),
				'public'  => $public,
			];
		}

		$default  = '# This file is automatically added by Rank Math SEO plugin to help a website index better';
		$default .= "\n# More info: https://s.rankmath.com/home\n";
		$default .= "User-Agent: *\n";
		if ( 0 === $public ) {
			$default .= "Disallow: /\n";
		} else {
			$default .= "Disallow: /wp-admin/\n";
			$default .= "Allow: /wp-admin/admin-ajax.php\n";
		}

		return [
			'exists'  => false,
			'default' => apply_filters( 'robots_txt', $default, $public ),
			'public'  => $public,
		];
	}
}
