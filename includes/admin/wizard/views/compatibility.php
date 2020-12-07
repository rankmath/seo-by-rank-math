<?php
/**
 * Setup wizard compatibility step.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

use RankMath\Helper;
use RankMath\KB;

defined( 'ABSPATH' ) || exit;

global $wp_version;

update_option( 'rank_math_wizard_completed', true );

$php_version           = phpversion();
$php_version_ok        = version_compare( $php_version, rank_math()->php_version, '>' );
$php_version_recommend = version_compare( $php_version, '7', '<' );

$dom_ext       = extension_loaded( 'dom' );
$simplexml_ext = extension_loaded( 'SimpleXML' );
$gd_ext        = extension_loaded( 'gd' );
$mb_string     = extension_loaded( 'mbstring' );
$openssl       = extension_loaded( 'openssl' );
$all_good      = $php_version_ok && $dom_ext && $simplexml_ext && $gd_ext && $mb_string && $openssl;

?>

<?php $wizard->cmb->show_form(); ?>

<?php

if ( $all_good ) :
	?>
<br>
<h2 class="text-center compatibility-check">
	<i class="dashicons <?php echo $php_version_recommend ? 'dashicons-warning' : 'dashicons-yes'; ?>"></i> <?php esc_html_e( 'Your website is compatible to run Rank Math SEO', 'rank-math' ); ?>
	<a href="#" data-target="rank-math-compatibility-collapsible" class="rank-math-collapsible-trigger">
		<span class="dashicons dashicons-arrow-down-alt2"><span><?php esc_html_e( 'More', 'rank-math' ); ?></span></span>
		<span class="dashicons dashicons-arrow-up-alt2"><span><?php esc_html_e( 'Less', 'rank-math' ); ?></span></span>
	</a>
</h2>
<div id="rank-math-compatibility-collapsible" class="rank-math-collapsible-content">
<?php endif; ?>

	<table class="form-table">
		<tr class="check-<?php echo $php_version_ok ? 'yes' : 'no'; ?>">
			<th>
				<?php
				if ( $php_version_ok ) {
					/* translators: php version */
					printf( esc_html__( 'Your PHP Version: %s', 'rank-math' ), $php_version );
					if ( $php_version_recommend ) {
						?>
						<?php echo ' | ' . esc_html__( 'Recommended: PHP 7.2 or later', 'rank-math' ); ?>
						<p class="description">
						<?php
							echo ( ! Helper::is_whitelabel() ) ?
								esc_html__( 'Rank Math is compatible with your PHP version but we recommend updating to PHP 7.2 for increased speed and security.', 'rank-math' ) . ' <a href="' . KB::get( 'rm-requirements' ) . '" target="_blank">' . esc_html__( 'More information', 'rank-math' ) . '</a>' :
								esc_html__( 'This plugin is compatible with your PHP version but we recommend updating to PHP 7.2 for increased speed and security.', 'rank-math' );
						?>
						</p>
						<?php
					}
				} else {
					/* translators: php version */
					printf( esc_html__( 'Your PHP Version: %s | Recommended version: 7.2 | Minimal required: 5.4', 'rank-math' ), $php_version );
				}
				?>
			</th>
			<td><span class="dashicons dashicons-<?php echo $php_version_ok ? ( $php_version_recommend ? 'warning' : 'yes' ) : 'no'; ?>"></span></td>
		</tr>
		<tr class="check-yes">
			<th>
				<?php
				echo esc_html__( 'You are using minimum recommended WordPress version.', 'rank-math' );
				?>
			</th>
			<td><span class="dashicons dashicons-yes"></span></td>
		</tr>
		<tr class="check-<?php echo $dom_ext ? 'yes' : 'no'; ?>">
			<th>
				<?php echo $dom_ext ? esc_html__( 'PHP DOM Extension installed', 'rank-math' ) : esc_html__( 'PHP DOM Extension missing', 'rank-math' ); ?>
			</th>
			<td><span class="dashicons dashicons-<?php echo $dom_ext ? 'yes' : 'no'; ?>"></span></td>
		</tr>
		<tr class="check-<?php echo $simplexml_ext ? 'yes' : 'no'; ?>">
			<th>
				<?php echo $simplexml_ext ? esc_html__( 'PHP SimpleXML Extension installed', 'rank-math' ) : esc_html__( 'PHP SimpleXML Extension missing', 'rank-math' ); ?>
			</th>
			<td><span class="dashicons dashicons-<?php echo $simplexml_ext ? 'yes' : 'no'; ?>"></span></td>
		</tr>
		<tr class="check-<?php echo $gd_ext ? 'yes' : 'no'; ?>">
			<th>
				<?php echo $gd_ext ? esc_html__( 'PHP GD Extension installed', 'rank-math' ) : esc_html__( 'PHP GD Extension missing', 'rank-math' ); ?>
			</th>
			<td><span class="dashicons dashicons-<?php echo $gd_ext ? 'yes' : 'no'; ?>"></span></td>
		</tr>
		<tr class="check-<?php echo $mb_string ? 'yes' : 'no'; ?>">
			<th>
				<?php echo $mb_string ? esc_html__( 'PHP MBstring Extension installed', 'rank-math' ) : esc_html__( 'PHP MBstring Extension missing', 'rank-math' ); ?>
			</th>
			<td><span class="dashicons dashicons-<?php echo $mb_string ? 'yes' : 'no'; ?>"></span></td>
		</tr>
		<tr class="check-<?php echo $openssl ? 'yes' : 'no'; ?>">
			<th>
				<?php echo $openssl ? esc_html__( 'PHP OpenSSL Extension installed', 'rank-math' ) : esc_html__( 'PHP OpenSSL Extension missing', 'rank-math' ); ?>
			</th>
			<td><span class="dashicons dashicons-<?php echo $mb_string ? 'yes' : 'no'; ?>"></span></td>
		</tr>
	</table>
	<?php if ( $all_good ) { ?>
		<p class="description checklist-ok">
		<?php
			echo ( ! Helper::is_whitelabel() ) ?
				esc_html__( 'Your server is correctly configured to use Rank Math.', 'rank-math' ) :
				esc_html__( 'Your server is correctly configured to use this plugin.', 'rank-math' );
		?>
		</p>
	<?php } else { ?>
		<p class="description checklist-not-ok">
		<?php
			echo ( ! Helper::is_whitelabel() ) ?
				esc_html__( 'Please resolve the issues above to be able to use all features of Rank Math plugin. If you are not sure how to do it, please contact your hosting provider.', 'rank-math' ) :
				esc_html__( 'Please resolve the issues above to be able to use all SEO features. If you are not sure how to do it, please contact your hosting provider.', 'rank-math' );
		?>
		</p>
	<?php } ?>

	<?php
	//
	// PLUGINS.
	//
	$conflicting_plugins = $this->get_conflicting_plugins();
	?>
	<?php if ( $conflicting_plugins ) : ?>
		<p class="conflict-text">
			<?php
				echo ( ! Helper::is_whitelabel() ) ?
					esc_html__( 'The following active plugins on your site may cause conflict issues when used alongside Rank Math: ', 'rank-math' ) :
					esc_html__( 'The following active plugins on your site may cause conflict issues when used alongside this plugin: ', 'rank-math' );
			?>
		</p>
		<table class="form-table wp-core-ui wizard-conflicts">
			<?php foreach ( $conflicting_plugins as $pk => $plugin ) { ?>
				<tr>
					<td><span class="dashicons dashicons-warning"></span></td>
					<td><?php echo $plugin . ( in_array( $pk, [ 'all-in-one-schemaorg-rich-snippets/index.php', 'wordpress-seo/wp-seo.php', 'wordpress-seo-premium/wp-seo-premium.php', 'all-in-one-seo-pack/all_in_one_seo_pack.php' ], true ) ? '<span class="import-info">' . esc_html__( 'You can import settings in the next step.', 'rank-math' ) . '</span>' : '' ); ?></td>
					<td><a href="#" class="button button-small wizard-deactivate-plugin" data-plugin="<?php echo esc_attr( $pk ); ?>"><?php esc_html_e( 'Deactivate Plugin', 'rank-math' ); ?></a></td>
				</tr>
			<?php } ?>
		</table>
		<?php
			set_transient( '_rank_math_conflicting_plugins', array_keys( $conflicting_plugins ) );
		else :
			delete_transient( '_rank_math_conflicting_plugins' );
			?>
		<p class="conflict-text noconflict"><?php esc_html_e( 'No known conflicting plugins found.', 'rank-math' ); ?></p>
	<?php endif; ?>

<?php if ( $all_good ) : ?>
</div> <!-- /collapsible -->
<?php endif; ?>

<footer class="form-footer rank-math-custom wp-core-ui rank-math-ui text-center">
	<?php if ( $all_good ) : ?>
	<button type="submit" class="button button-primary button-animated"><?php esc_html_e( 'Start Wizard', 'rank-math' ); ?> <i class="dashicons dashicons-arrow-right-alt2"></i></button>
	<?php endif; ?>
</footer>
