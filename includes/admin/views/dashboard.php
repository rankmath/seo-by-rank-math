<?php
/**
 * Dashboard page template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

use MyThemeShop\Helpers\Param;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

$is_network_admin  = is_network_admin();
$is_network_active = RankMath\Helper::is_plugin_active_for_network();
$current_tab       = $is_network_active && $is_network_admin ? 'help' : Param::get( 'view', 'modules' );

// Header.
rank_math()->admin->display_admin_header();
?>
<div class="wrap rank-math-wrap dashboard">

	<span class="wp-header-end"></span>

	<?php rank_math()->admin->display_dashboard_nav(); ?>

	<?php
	if ( $is_network_active && ! $is_network_admin && 'help' === $current_tab ) {
		return;
	}

	// phpcs:disable
	// Display modules activation and deactivation form.
	if ( 'modules' === $current_tab ) {
		rank_math()->manager->display_form();

	// Others.
	} else {
		$file = apply_filters( 'rank_math/admin/dashboard_view', Admin_Helper::get_view( "dashboard-{$current_tab}" ), $current_tab );
		if ( file_exists( $file ) ) {
			include_once $file;
		}
	}
	// phpcs:enable
	?>
</div>
