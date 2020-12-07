<?php
/**
 * Beta Opt-in view in Version Control Tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Version_Control
 */

?>

<form id="cmb2-metabox-rank-math-beta-optin" class="rank-math-beta-optin-form cmb2-form rank-math-box" action="" method="post">

	<header>
		<h3><?php esc_html_e( 'Beta Opt-in', 'rank-math' ); ?></h3>
	</header>

	<p><?php esc_html_e( 'You can take part in shaping Rank Math by test-driving the newest features and letting us know what you think. Turn on the Beta Tester feature to get notified about new beta releases. The beta version will not install automatically and you always have the option to ignore it.', 'rank-math' ); ?></p>
	<?php // translators: Warning. ?>
	<p class="description warning"><strong><?php printf( esc_html__( '%s It is not recommended to use the beta version on live production sites.', 'rank-math' ), '<span class="rollback-warning">' . esc_html__( 'Warning: ', 'rank-math' ) . '</span>' ); ?></strong></p>

	<table class="form-table">
		<tbody>
			<tr class="cmb-row cmb-type-switch">
				<th scope="row"><label><?php esc_html_e( 'Beta Tester', 'rank-math' ); ?></label></th>
				<td>
					<ul class="cmb2-radio-list cmb2-list">
						<li>
							<input type="radio" class="cmb2-option" name="beta_optin" id="beta_optin1" value="off" <?php checked( ! $beta_optin ); ?>>
							<label for="beta_optin1"><?php esc_html_e( 'Off', 'rank-math' ); ?></label>
						</li>
						<li>
							<input type="radio" class="cmb2-option" name="beta_optin" id="beta_optin2" value="on" <?php checked( $beta_optin ); ?>>
							<label for="beta_optin2"><?php esc_html_e( 'On', 'rank-math' ); ?></label>
						</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>

	<footer>
		<?php wp_nonce_field( 'rank-math-beta-optin' ); ?>
		<button type="submit" class="button button-primary button-xlarge"><?php esc_html_e( 'Save Changes', 'rank-math' ); ?></button>
	</footer>

</form>
