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
use RankMath\Search_Console\Data_Fetcher;

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
			<h1><?php esc_html_e( 'Google&trade; Search Console', 'rank-math' ); ?> </h1>
			<p>
				<?php
				/* translators: Link to How to Setup Google Search Console KB article */
				printf( esc_html__( 'Verify your site on Google Search Console and connect it here to see crawl error notifications, keyword statistics and other important information right in your WordPress dashboard. %s', 'rank-math' ), '<a href="' . KB::get( 'search-console' ) . '" target="_blank">' . esc_html__( 'Read more about it here.', 'rank-math' ) . '</a>' );
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
		$dep  = '';
		$data = Helper::search_console_data();

		if ( ! Helper::is_module_active( 'search-console' ) ) {
			$wizard->cmb->add_field(
				[
					'id'      => 'search-console',
					'type'    => 'toggle',
					'name'    => esc_html__( 'Search Console', 'rank-math' ),
					'desc'    => esc_html__( 'Connect Rank Math with Google Search Console to see the most important information from Google directly in your WordPress dashboard.', 'rank-math' ),
					'default' => 'off',
				]
			);
			$dep = [ [ 'search-console', 'on' ] ];
		}

		$wizard->cmb->add_field(
			[
				'id'         => 'console_authorization_code',
				'type'       => 'text',
				'name'       => esc_html__( 'Search Console', 'rank-math' ),
				'attributes' => [ 'data-authorized' => $data['authorized'] ? 'true' : 'false' ],
				'after'      => $this->get_buttons(),
				'dep'        => $dep,
				'classes'    => $data['authorized'] ? 'authorized' : 'unauthorized',
			]
		);

		$profile       = Helper::get_settings( 'general.console_profile' );
		$profile_label = str_replace( 'sc-domain:', __( 'Domain Property: ', 'rank-math' ), $profile );
		foreach ( $data['profiles'] as $key => $value ) {
			$data['profiles'][ $key ] = str_replace( 'sc-domain:', __( 'Domain Property: ', 'rank-math' ), $value );
		}

		$wizard->cmb->add_field(
			[
				'id'           => 'console_profile',
				'type'         => 'select',
				'name'         => esc_html__( 'Search Console Profile', 'rank-math' ),
				'desc'         => esc_html__( 'After authenticating with Google Search Console, select your website from the dropdown list.', 'rank-math' ) .
					' <span id="gsc-dp-info" class="hidden">' . __( 'Please note that the Sitemaps overview in the Search Console module will not be available when using a Domain Property.', 'rank-math' ) . '</span>' .
					/* translators: Link to setting screen */
					'<br><br><span style="color: orange;">' . sprintf( __( 'Is your site not listed? <a href="%1$s" target="_blank">Click here</a> to get your website verified.', 'rank-math' ), Helper::get_admin_url( 'options-general#setting-panel-webmaster' ) ) . '</span>',
				'options'      => $profile ? [ $profile => $profile_label ] : $data['profiles'],
				'default'      => $profile,
				'before_field' => '<button class="button button-primary hidden rank-math-refresh" ' . ( $data['authorized'] ? '' : 'disabled="disabled"' ) . '>' . esc_html__( 'Refresh Sites', 'rank-math' ) . '</button>',
				'attributes'   => $data['authorized'] ? [ 'data-s2' => '' ] : [
					'disabled' => 'disabled',
					'data-s2'  => '',
				],
				'dep'          => $dep,
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
		$module   = 'off';
		$settings = rank_math()->settings->all_raw();

		if ( isset( $values['console_profile'] ) ) {
			$module                                 = 'on';
			$settings['general']['console_profile'] = $values['console_profile'];
			Data_Fetcher::get()->clean_start();
		}

		Helper::update_modules( [ 'search-console' => $module ] );
		Helper::update_all_settings( $settings['general'], null, null );

		return true;
	}

	/**
	 * Get buttons html.
	 *
	 * @return string
	 */
	private function get_buttons() {
		$data      = Helper::search_console_data();
		$primary   = '<button class="button button-primary rank-math-authorize-account">' . ( $data['authorized'] ? esc_html__( 'De-authorize Account', 'rank-math' ) : esc_html__( 'Authorize', 'rank-math' ) ) . '</button>';
		$secondary = '<a href="' . esc_url( Helper::get_console_auth_url() ) . '" class="button button-primary custom rank-math-get-authorization-code"' . ( $data['authorized'] ? ' style="display:none;"' : '' ) . '>' . esc_html__( 'Get Authorization Code', 'rank-math' ) . '</a><br />';
		/* translators: Link to 'How To Create a Google API Project For Connecting Search Console' KB article */
		$desc = '<div class="cmb2-metabox-description">' . sprintf( esc_html__( 'You can also create your own app and connect that instead. %s', 'rank-math' ), '<a href="' . KB::get( 'custom-gsc-project' ) . '" target="_blank">' . esc_html__( 'Follow this tutorial.', 'rank-math' ) . '</a>' ) . '</div>';
		return $primary . $secondary . $desc;
	}
}
