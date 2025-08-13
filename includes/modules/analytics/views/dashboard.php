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

$path = rank_math()->admin_dir() . 'wizard/views/'; // phpcs:ignore
?>
<div class="analytics">

	<span class="wp-header-end"></span>

	<?php
	if ( ! Helper::is_site_connected() ) {
		require_once $path . 'rank-math-connect.php';
	} elseif ( ! Authentication::is_authorized() ) {
		require_once $path . 'google-connect.php';
	} else {
		echo '<div class="" id="rank-math-analytics"></div>';
	}
	?>

</div>
