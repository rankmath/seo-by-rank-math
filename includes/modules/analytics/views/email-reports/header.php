<?php
/**
 * Analytics Report header template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

defined( 'ABSPATH' ) || exit;

?><!doctype html>
<html>
	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php esc_html_e( 'SEO Report of Your Website', 'rank-math' ); ?></title>

		<?php $this->template_part( 'style' ); ?>
	</head>
	<body class="">
		<span class="preheader"><?php esc_html_e( 'SEO Report of Your Website', 'rank-math' ); ?></span>
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
			<tr>
				<td>&nbsp;</td>
				<td class="container">
					<div class="content">

						<!-- START CENTERED WHITE CONTAINER -->
						<table role="presentation" class="main" border="0" cellpadding="0" cellspacing="0">

							<!-- START HEADER -->
							<tr>
								<td class="header">
									<table role="presentation" border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td class="logo">
												<a href="###LOGO_LINK###" target="_blank">
													<?php $this->image( 'report-logo.png', 0, 26, __( 'Rank Math', 'rank-math' ) ); ?>
												</a>
											</td>
											<td class="period-days">
												<?php // Translators: don't translate the variable names between the #hashes#. ?>
												<?php esc_html_e( 'Last ###PERIOD_DAYS### Days', 'rank-math' ); ?>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<!-- END HEADER -->

							<!-- START MAIN CONTENT AREA -->
							<tr>
								<td class="wrapper">
									<table role="presentation" border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td>
