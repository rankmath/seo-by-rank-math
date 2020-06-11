<?php
/**
 * Dashboard help tab template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

use RankMath\KB;

use RankMath\Helper;

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

if ( Helper::has_cap( 'general' ) ) {
	include_once 'plugin-activation.php';
}

include_once 'plugin-activation.php'; ?>

	<div class="two-col rank-math-box-help">

		<div class="col rank-math-box">

			<header>
				<h3><?php esc_html_e( 'Next steps&hellip;', 'rank-math' ); ?></h3>
			</header>

			<div class="rank-math-box-content">

				<ul class="rank-math-list-icon">

					<li>
						<a href="<?php KB::the( 'how-to-setup' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-settings"></i>
							<div>
								<strong><?php esc_html_e( 'Setup Rank Math', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'How to Properly Setup Rank Math', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

					<li>
						<a href="<?php KB::the( 'seo-import' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-import"></i>
							<div>
								<strong><?php esc_html_e( 'Import', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'How to Import Data from Your Previous SEO Plugin', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

					<li>
						<a href="<?php KB::the( 'score-100' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-post"></i>
							<div>
								<strong><?php esc_html_e( 'Post Screen', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'How to Make Your Posts Pass All the Tests', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

				</ul>

				<a class="button button-secondary button-xlarge" href="<?php KB::the( 'rm-kb' ); ?>" target="_blank"><?php esc_html_e( 'Visit Knowledge Base', 'rank-math' ); ?></a>

			</div>

		</div>

		<div class="col rank-math-box">

			<header>
				<h3><?php esc_html_e( 'Product Support', 'rank-math' ); ?></h3>
			</header>

			<div class="rank-math-box-content">

				<ul class="rank-math-list-icon">

					<li>
						<a href="<?php KB::the( 'rm-kb' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-help"></i>
							<div>
								<strong><?php esc_html_e( 'Online Documentation', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'Understand all the capabilities of Rank Math', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

					<li>
						<a href="https://s.rankmath.com/documentation" target="_blank">
							<i class="rm-icon rm-icon-comments"></i>
							<div>
								<strong><?php esc_html_e( 'Browse FAQ\'s', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'Find answers to the most commonly asked questions.', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

					<li>
						<a href="<?php KB::the( 'rm-support' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-support"></i>
							<div>
								<strong><?php esc_html_e( 'Ticket Support', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'Direct help from our qualified support team', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

				</ul>

				<a class="button button-secondary button-xlarge" href="<?php KB::the( 'rm-support' ); ?>" target="_blank"><?php esc_html_e( 'Visit Support Center', 'rank-math' ); ?></a>

			</div>

		</div>

	</div><!--.two-col-->

</div><!--.dashboard-wrapper-->
