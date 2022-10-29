<?php
/**
 * Dashboard help tab template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

use RankMath\KB;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

if ( Helper::has_cap( 'general' ) ) {
	include_once 'plugin-activation.php';
}

require_once 'plugin-activation.php'; ?>

	<div class="two-col rank-math-box-help">

		<div class="col rank-math-box">

			<header>
				<h3><?php esc_html_e( 'Next steps&hellip;', 'rank-math' ); ?></h3>
			</header>

			<div class="rank-math-box-content">

				<ul class="rank-math-list-icon">

					<li>
					<?php if ( ! defined( 'RANK_MATH_PRO_FILE' ) ) { ?>
						<a href="<?php KB::the( 'pro', 'Help Tab PRO Link' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-star-filled"></i>
							<div>
								<strong><?php esc_html_e( 'Upgrade to PRO', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'Advanced Schema, Analytics and much more...', 'rank-math' ); ?></p>
							</div>
						</a>
					<?php } else { ?>
						<a href="<?php KB::the( 'how-to-setup', 'Help Tab Setup KB' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-settings"></i>
							<div>
								<strong><?php esc_html_e( 'Setup Rank Math', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'How to Properly Setup Rank Math', 'rank-math' ); ?></p>
							</div>
						</a>
					<?php } ?>
					</li>

					<li>
						<a href="<?php KB::the( 'seo-import', 'Help Tab Import Data' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-import"></i>
							<div>
								<strong><?php esc_html_e( 'Import Data', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'How to Import Data from Your Previous SEO Plugin', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

					<li>
						<a href="<?php KB::the( 'score-100', 'Help Tab Score KB' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-post"></i>
							<div>
								<strong><?php esc_html_e( 'Improve SEO Score', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'How to Make Your Posts Pass All the Tests', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

				</ul>
			</div>

		</div>

		<div class="col rank-math-box">

			<header>
				<h3><?php esc_html_e( 'Product Support', 'rank-math' ); ?></h3>
			</header>

			<div class="rank-math-box-content">

				<ul class="rank-math-list-icon">

					<li>
						<a href="<?php KB::the( 'kb-seo-suite', 'Help Tab KB Link' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-help"></i>
							<div>
								<strong><?php esc_html_e( 'Online Documentation', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'Understand all the capabilities of Rank Math', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

					<li>
						<a href="<?php KB::the( 'support', 'Help Tab Ticket' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-support"></i>
							<div>
								<strong><?php esc_html_e( 'Ticket Support', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'Direct help from our qualified support team', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

					<li>
						<a href="<?php KB::the( 'help-affiliate', 'Help Tab Aff Link' ); ?>" target="_blank">
							<i class="rm-icon rm-icon-sitemap"></i>
							<div>
								<strong><?php esc_html_e( 'Affiliate Program', 'rank-math' ); ?></strong>
								<p><?php esc_html_e( 'Earn flat 30% on every sale!', 'rank-math' ); ?></p>
							</div>
						</a>
					</li>

				</ul>

			</div>

		</div>

	</div><!--.two-col-->

</div><!--.dashboard-wrapper-->
