<?php
/**
 * Global functionality of the plugin.
 *
 * Defines the functionality loaded both on admin and frontend.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Paper\Paper;
use RankMath\Traits\Hooker;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Common class.
 */
class Auto_Updater {

	use Hooker;

	/**
	 * Constructor method.
	 */
	public function __construct() {
		$this->filter( 'auto_update_plugin', 'auto_update_plugin', 10, 2 );
		$this->action( 'upgrader_process_complete', 'upgrader_process_complete', 10, 2 );
	}

	/**
	 * Auto update the plugin.
	 *
	 * @param bool  $update Whether to update the plugin or not.
	 * @param array $item  The update plugin object.
	 *
	 * @return bool
	 */
	public function auto_update_plugin( $update, $item ) {
		if (
			isset( $item->slug ) &&
			'seo-by-rank-math' === $item->slug &&
			isset( $item->new_version ) &&
			false === stripos( $item->new_version, 'beta' )
		) {
			return true;
		}

		return $update;
	}

	/**
	 * Send update notification email after completing the update process.
	 *
	 * @param  object $plugin_upgrader_obj Plugin_Upgrader object.
	 * @param  [type] $hook_extra          Additional information about the process.
	 */
	public function upgrader_process_complete( $plugin_upgrader_obj, $hook_extra ) {
		// Check if we just updated Rank Math.
		if ( ! isset( $hook_extra['plugin'] ) || 'seo-by-rank-math/rank-math.php' !== $hook_extra['plugin'] ) {
			return;
		}

		// Check if it was a regular auto-update.
		if ( ! is_a( $plugin_upgrader_obj->skin, 'Automatic_Upgrader_Skin' ) ) {
			return;
		}

		$this->send_update_email( $plugin_upgrader_obj );
	}

	/**
	 * Send the email using wp_mail().
	 *
	 * @param object $plugin_upgrader_obj Plugin_Upgrader object of the finished update process.
	 */
	public function send_update_email( $plugin_upgrader_obj ) {
		if ( ! $this->do_filter( 'auto_update_send_email', Helper::get_settings( 'general.enable_auto_update_email' ), $plugin_upgrader_obj ) ) {
			return;
		}

		// Extract version number.
		preg_match( '/seo-by-rank-math\.([0-9.]+)/', $plugin_upgrader_obj->result['source'], $matches );
		$version = $matches[1];

		$to_address = get_site_option( 'admin_email' );
		$registered = Admin_Helper::get_registration_data();
		if ( is_array( $registered ) && ! empty( $registered['email'] ) ) {
			$to_address = $registered['email'];
		}

		// Translators: placeholder is the new version number.
		$subject = sprintf( __( '(!) Rank Math SEO has been updated to %1$s', 'rank-math' ), $version );

		// Translators: 1 is the site URL, 2 is the new version number.
		$body  = sprintf( __( 'Hi! The Rank Math plugin installed on %1$s has been automatically updated to version %2$s.', 'rank-math' ), home_url(), $version );
		$body .= "\n\n";

		// Translators: placeholder is the new version number.
		$body   .= sprintf( __( 'No further action is needed on your part. For more on version %1$s, see the official changelog: https://rankmath.com/changelog/', 'rank-math' ), $version );
		$body   .= "\n\n";
		$body   .= __( 'If you experience any issues or need support, we are here to help: https://support.rankmath.com/', 'rank-math' );
		$body   .= "\n\n";
		$body   .= __( 'Thank you for using Rank Math.', 'rank-math' );
		$headers = '';

		$email = compact( 'to_address', 'subject', 'body', 'headers' );
		$email = $this->do_filter( 'auto_update_email', $email, $version, $plugin_upgrader_obj );

		wp_mail( $email['to_address'], wp_specialchars_decode( $email['subject'] ), $email['body'], $email['headers'] );
	}
}
