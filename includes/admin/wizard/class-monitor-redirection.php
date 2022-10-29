<?php
/**
 * The Monitor Redirection wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\KB;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Monitor_Redirection implements Wizard_Step {

	/**
	 * Render step body.
	 *
	 * @param object $wizard Wizard class instance.
	 *
	 * @return void
	 */
	public function render( $wizard ) {
		?>
		<?php $wizard->cmb->show_form(); ?>

		<footer class="form-footer wp-core-ui">
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

		// 404 Monitor Title.
		$wizard->cmb->add_field(
			[
				'id'      => '404_monitor_title',
				'type'    => 'raw',
				'content' => sprintf( '<div class="cmb-row monitor-header text-center"><h1>%1$s</h1><div class="monitor-desc text-center">%2$s</div>', esc_html__( '404 Monitor', 'rank-math' ), esc_html__( 'Set default values for the 404 error monitor here.', 'rank-math' ) . '</div>' ),
			]
		);

		// 404 Monitor.
		$wizard->cmb->add_field(
			[
				'id'      => '404_monitor',
				'type'    => 'toggle',
				'name'    => esc_html__( '404 Monitor', 'rank-math' ),
				/* translators: Link to kb article */
				'desc'    => __( 'The 404 monitor will let you see if visitors or search engines bump into any <code>404 Not Found</code> error while browsing your site.', 'rank-math' ),
				'default' => Helper::is_module_active( '404-monitor' ) ? 'on' : 'off',
			]
		);

		// Redirections.
		$wizard->cmb->add_field(
			[
				'id'      => 'redirection_title',
				'type'    => 'raw',
				'content' => sprintf( '<br><div class="cmb-row redirections-header text-center" style="border-top:0;"><h1>%1$s</h1><div class="redirections-desc text-center">%2$s %3$s</div>', esc_html__( 'Redirections', 'rank-math' ), esc_html__( 'Set default values for the redirection module from here.', 'rank-math' ), '<a href="' . KB::get( 'redirections', 'SW Redirection Step' ) . '" target="_blank">' . esc_html__( 'Learn more about Redirections.', 'rank-math' ) . '</a></div>' ),
			]
		);

		$wizard->cmb->add_field(
			[
				'id'      => 'redirections',
				'type'    => 'toggle',
				'name'    => esc_html__( 'Redirections', 'rank-math' ),
				'desc'    => esc_html__( 'Set up temporary or permanent redirections. Combined with the 404 monitor, you can easily redirect faulty URLs on your site, or add custom redirections.', 'rank-math' ),
				'default' => Helper::is_module_active( 'redirections' ) ? 'on' : 'off',
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
		Helper::update_modules(
			[
				'404-monitor'  => $values['404_monitor'],
				'redirections' => $values['redirections'],
			]
		);

		return true;
	}
}
