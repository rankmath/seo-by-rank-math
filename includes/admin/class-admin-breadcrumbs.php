<?php
/**
 * Breadcrumbs for the Rank Math pages
 *
 * @since      1.0.44
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Header class.
 *
 * @codeCoverageIgnore
 */
class Admin_Breadcrumbs {

	/**
	 * Display Header.
	 */
	public function display() {
		?>
		<div class="rank-math-breadcrumbs-wrap">
		<div class="rank-math-breadcrumbs">
				<span><?php echo esc_html__( 'Dashboard', 'rank-math' ); ?></span>
				<span class="divider">/</span>
				<span class="active"><?php echo esc_html( $this->get_page_title() ); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Get Current Admin Page Title.
	 */
	private function get_page_title() {
		$base = __( 'Modules', 'rank-math' );
		if ( is_network_admin() ) {
			$base = __( 'Help', 'rank-math' );
		}
		$default = 'rank-math' === Param::get( 'page' ) ? $base : get_admin_page_title();
		return str_replace( '_', ' ', Param::get( 'view', $default, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK ) );
	}
}
