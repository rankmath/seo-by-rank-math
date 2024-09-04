<?php
/**
 * The Search Console wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Analytics\Workflow\Workflow;
use RankMath\Analytics\Workflow\Console;
use RankMath\Analytics\Workflow\Inspections;
use RankMath\Analytics\Workflow\Objects;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Search_Console implements Wizard_Step {

	/**
	 * Render step body.
	 *
	 * @param object $wizard Wizard class instance.
	 *
	 * @return void
	 */
	public function render( $wizard ) {
		?>
		<header>
			<h1><?php esc_html_e( 'Connect Google&trade; Services', 'rank-math' ); ?> </h1>
			<p>
				<?php
				/* translators: Link to How to Setup Google Search Console KB article */
				printf( esc_html__( 'Rank Math automates everything, use below button to connect your site with Google Search Console and Google Analytics. It will verify your site and submit sitemaps automatically. %s', 'rank-math' ), '<a href="' . esc_url( KB::get( 'help-analytics', 'SW Analytics Step Description' ) ) . '" target="_blank">' . esc_html__( 'Read more about it here.', 'rank-math' ) . '</a>' );
				?>
			</p>
		</header>

		<?php $wizard->cmb->show_form(); ?>

		<footer class="form-footer wp-core-ui rank-math-ui">
			<?php $wizard->get_skip_link(); ?>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save and Continue', 'rank-math' ); ?></button>
		</footer>
		<?php
	}

	/**
	 * Render form for step.
	 *
	 * @param object $wizard Wizard class instance.
	 *
	 * @return void
	 */
	public function form( $wizard ) {
		$wizard->cmb->add_field(
			[
				'id'   => 'search_console_ui',
				'type' => 'raw',
				'file' => __DIR__ . '/views/search-console-ui.php',
			]
		);
	}

	/**
	 * Save handler for step.
	 *
	 * @param array  $values Values to save.
	 * @param object $wizard Wizard class instance.
	 *
	 * @return bool
	 */
	public function save( $values, $wizard ) {
		$settings = rank_math()->settings->all_raw();

		$settings['general']['console_email_reports'] = Param::post( 'console_email_reports' );

		Helper::update_all_settings( $settings['general'], null, null );

		// For Search console.
		$value = [
			'country'             => Param::post( 'site-console-country' ),
			'profile'             => Param::post( 'site-console-profile' ),
			'enable_index_status' => Param::post( 'enable-index-status' ),
		];
		update_option( 'rank_math_google_analytic_profile', $value );

		// For Analytics.
		$analytic_value = [
			'adsense_id'       => Param::post( 'site-adsense-account' ),
			'account_id'       => Param::post( 'site-analytics-account' ),
			'property_id'      => Param::post( 'site-analytics-property' ),
			'view_id'          => Param::post( 'site-analytics-view' ),
			'measurement_id'   => Param::post( 'measurementID' ),
			'stream_name'      => Param::post( 'streamName' ),
			'country'          => Param::post( 'site-analytics-country' ),
			'install_code'     => 'on' === Param::post( 'install-code' ) ? true : false,
			'anonymize_ip'     => 'on' === Param::post( 'anonymize-ip' ) ? true : false,
			'local_ga_js'      => 'on' === Param::post( 'local-ga-js' ) ? true : false,
			'exclude_loggedin' => 'on' === Param::post( 'exclude-loggedin' ) ? true : false,
		];
		update_option( 'rank_math_google_analytic_options', $analytic_value );

		new Objects();
		new Console();
		new Inspections();

		return true;
	}
}
