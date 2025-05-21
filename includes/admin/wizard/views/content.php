<?php
/**
 * Setup wizard content template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

use RankMath\KB;

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
	<body class="rank-math-wizard rank-math-page <?php echo is_rtl() ? ' rtl' : ''; ?>">
		<div id="rank-math-wizard-wrapper"></div>
	</body>
	<?php
	rank_math()->json->output();
	if ( function_exists( 'wp_print_media_templates' ) ) {
		wp_print_media_templates();
	}
	wp_print_footer_scripts();
	?>
</html>
