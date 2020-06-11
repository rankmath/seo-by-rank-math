<?php
/**
 * Help page template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

/**
 * Add new help content tabs on help and support page.
 *
 * @param array $help_content
 */
$tabs = apply_filters(
	'rank_math/help/tabs',
	array(
		'getting-started' => array(
			'title' => esc_html__( 'Getting started', 'rank-math' ),
			'view'  => 'help/getting-started.php',
		),
		'amp'             => array(
			'title' => esc_html__( 'AMP', 'rank-math' ),
			'view'  => 'help/amp.php',
		),
		'local-seo'       => array(
			'title' => esc_html__( 'Local SEO', 'rank-math' ),
			'view'  => 'help/local-seo.php',
		),
	)
);
// Header.
rank_math()->admin->display_admin_header();
?>
<div class="wrap rank-math-wrap">

	<span class="wp-header-end"></span>

	<div class="container">

		<div id="rank-math-help-wrapper" class="rank-math-tabs">

			<header class="cmb-row tab-header">
				<h3><?php esc_html_e( 'Help &amp; Support', 'rank-math' ); ?></h3>
			</header>

			<div class="rank-math-tabs-navigation wp-clearfix">
				<?php foreach ( $tabs as $id => $tab ) : ?>
				<a href="#help-panel-<?php echo $id; ?>"><?php echo $tab['title']; ?></a>
				<?php endforeach; ?>
			</div>

			<div class="rank-math-tabs-content">
				<?php foreach ( $tabs as $id => $tab ) : ?>
				<div id="help-panel-<?php echo $id; ?>" class="rank-math-tab">
					<?php include $tab['view']; ?>
				</div>
				<?php endforeach; ?>
			</div>

		</div>

	</div>

</div>
