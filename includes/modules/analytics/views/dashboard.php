<?php
/**
 * Dashboard page template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

use RankMath\Helper;
use RankMath\Google\Authentication;

defined( 'ABSPATH' ) || exit;

// Header.
rank_math()->admin->display_admin_header();
$path = rank_math()->admin_dir() . 'wizard/views/'; // phpcs:ignore
?>
<div class="wrap rank-math-wrap analytics">

	<span class="wp-header-end"></span>

	<?php
	if ( ! Helper::is_site_connected() ) {
		require_once $path . 'rank-math-connect.php';
	} elseif ( ! Authentication::is_authorized() ) {
		require_once $path . 'google-connect.php';
	} else {
		echo '<div class="rank-math-analytics" id="rank-math-analytics"></div>';
	}
	?>

</div>
