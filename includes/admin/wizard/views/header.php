<?php
/**
 * Setup wizard header template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php esc_html_e( 'Setup Wizard - Rank Math', 'rank-math' ); ?></title>
	<?php wp_print_head_scripts(); ?>
	<?php wp_print_styles( 'rank-math-wizard' ); ?>
</head>
<body class="rank-math-wizard rank-math-page rank-math-wizard-body--<?php echo sanitize_html_class( $this->step_slug ); ?><?php echo is_rtl() ? ' rtl' : ''; ?><?php echo Helper::is_advanced_mode() ? ' rank-math-mode-advanced' : ' rank-math-mode-basic'; ?>">
