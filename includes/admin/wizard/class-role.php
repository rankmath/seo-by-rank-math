<?php
/**
 * The Role wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\Helper;
use RankMath\Role_Manager\Capability_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Role implements Wizard_Step {

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
			<h1><?php esc_html_e( 'Role Manager', 'rank-math' ); ?></h1>
			<p><?php esc_html_e( 'Set capabilities here.', 'rank-math' ); ?></p>
		</header>

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
		$wizard->cmb->add_field(
			[
				'id'      => 'role_manager',
				'type'    => 'toggle',
				'name'    => esc_html__( 'Role Manager', 'rank-math' ),
				/* translators: Link to kb article */
				'desc'    => __( 'The Role Manager allows you to use WordPress roles to control which of your site users can have edit or view access to Rank Math\'s settings.', 'rank-math' ),
				'default' => Helper::is_module_active( 'role-manager' ) ? 'on' : 'off',
			]
		);

		$defaults  = Helper::get_roles_capabilities();
		$caps      = Capability_Manager::get()->get_capabilities();
		$cap_count = count( $caps );

		foreach ( Helper::get_roles() as $role => $label ) {
			$default = isset( $defaults[ $role ] ) ? $defaults[ $role ] : [];
			$wizard->cmb->add_field(
				[
					'id'      => esc_attr( $role ),
					'type'    => 'multicheck_inline',
					'name'    => translate_user_role( $label ),
					'options' => $caps,
					'default' => $default,
					'classes' => 'cmb-big-labels' . ( count( $default ) === $cap_count ? ' multicheck-checked' : '' ),
					'dep'     => [ [ 'role_manager', 'on' ] ],
				]
			);
		}
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
		if ( empty( $values ) ) {
			return false;
		}

		Helper::update_modules( [ 'role-manager' => $values['role_manager'] ] );
		Helper::set_capabilities( $values );
		return true;
	}
}
