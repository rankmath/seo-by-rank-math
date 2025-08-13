<?php
/**
 * Status & Tools main view file.
 *
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 * @license    GPL-2.0+
 * @link       https://rankmath.com/wordpress/plugin/seo-suite/
 * @copyright  2019 Rank Math
 */

defined( 'ABSPATH' ) || exit;

use RankMath\Rollback_Version;

// Header.
if ( Rollback_Version::should_rollback() ) {
	$rollback = new Rollback_Version();
	$rollback->rollback();
	return;
}
?>
<div id="rank-math-tools-wrapper"></div>
