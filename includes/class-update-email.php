<?php
/**
 * Code related to the update notification emails.
 *
 * @since      1.0.57
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Traits\Hooker;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Common class.
 */
class Update_Email {

	use Hooker;

	/**
	 * Constructor method.
	 */
	public function __construct() {
		$this->filter( 'pre_set_site_transient_update_plugins', 'maybe_send_update_notification_email', 130 );
	}

	/**
	 * Maybe send the update notification email to the administrator if the setting is turned on.
	 *
	 * @param mixed $transient update_plugins site transient value.
	 * @return mixed
	 */
	public function maybe_send_update_notification_email( $transient ) {
		if ( ! Helper::get_settings( 'general.update_notification_email' ) ) {
			return $transient;
		}

		$should_send = $this->do_filter( 'admin/should_send_update_notification', $this->should_send_email( $transient ), $transient );
		if ( ! $should_send ) {
			return $transient;
		}

		$to = get_site_option( 'admin_email' );

		// Translators: placeholder is the site title.
		$subject = __( '[%s] An update is available for Rank Math', 'rank-math' );
		$subject = sprintf( $subject, wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );

		$body   = [];
		$body[] = __( 'Hello,', 'rank-math' ) . "\n";

		// Translators: placeholder is the site URL.
		$body[] = sprintf( __( 'This is an automated email to let you know that there is an update available for the Rank Math SEO plugin installed on: %s', 'rank-math' ), get_home_url() ) . "\n";

		$products = $this->do_filter( 'admin/update_notification_products', $this->get_updatable_products( $transient ), $transient );
		$list     = $this->get_products_list( $products );
		$body[]   = $list;

		// Translators: placeholder is the new admin page URL.
		$body[] = sprintf( __( 'To ensure your site is always on the latest, most up-to-date version of Rank Math - we recommend logging into the admin area to update the plugin as soon as possible: %s', 'rank-math' ), admin_url( 'update-core.php' ) ) . "\n";

		// Add a note about the support forums.
		$body[] = __( 'If you have any questions or experience any issues – our support team is at your disposal:', 'rank-math' );
		$body[] = __( 'https://support.rankmath.com/', 'rank-math' );
		$body[] = "\n" . '-';
		$body[] = __( 'Rank Math Team', 'rank-math' );

		$body = implode( "\n", $body );

		$headers = '';

		$email = compact( 'to', 'subject', 'body', 'headers' );
		$email = $this->do_filter( 'admin/update_notification_email', $email );

		$result = wp_mail( $email['to'], $email['subject'], $email['body'], $email['headers'] );

		$stored  = get_option( 'rank_math_update_notifications_sent', [] );
		$new_opt = $stored;
		foreach ( $products as $key => $value ) {
			$new_opt[ $key ] = $value;
		}
		update_option( 'rank_math_update_notifications_sent', $new_opt, false );

		return $transient;
	}

	/**
	 * Check if we should send an update email or not, based on the update_plugins transient value.
	 *
	 * @param mixed $transient Transient value.
	 * @return boolean
	 */
	public function should_send_email( $transient ) {
		// No need to send email if auto-update is enabled.
		if ( Helper::get_auto_update_setting() ) {
			return false;
		}

		if ( ! is_object( $transient )
			|| empty( $transient->response )
			|| empty( $transient->response['seo-by-rank-math/rank-math.php'] )
			|| empty( $transient->response['seo-by-rank-math/rank-math.php']->new_version )
		) {
			return false;
		}

		$new_version = $transient->response['seo-by-rank-math/rank-math.php']->new_version;

		// Now let's check if we've already sent this email.
		$sent = get_option( 'rank_math_update_notifications_sent', [ 'free' => [ 'new_version' => '1.0' ] ] );
		if ( ! isset( $sent['free'] ) ) {
			$sent['free'] = [ 'new_version' => '1.0' ];
		}
		if ( version_compare( $sent['free']['new_version'], $new_version, '>=' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get list of updatable products and their data.
	 *
	 * @param mixed $transient The update_plugins transient value.
	 * @return array
	 */
	public function get_updatable_products( $transient ) {
		if ( ! $this->should_send_email( $transient ) ) {
			return [];
		}

		$old_version = rank_math()->version;
		$new_version = $transient->response['seo-by-rank-math/rank-math.php']->new_version;

		$products = [
			'free' => [
				'name'        => __( 'Rank Math Free', 'rank-math' ),
				'old_version' => $old_version,
				'new_version' => $new_version,
				'changelog'   => __( 'https://rankmath.com/changelog/#free', 'rank-math' ),
			],
		];

		return $products;
	}

	/**
	 * Turn products array into a human-readable list.
	 *
	 * @param array $products_array Products array.
	 * @return string
	 */
	public function get_products_list( $products_array ) {
		$list = '';

		foreach ( $products_array as $product_data ) {
			// Translators: placeholders are the old and new version numbers.
			$list .= sprintf( __( '%1$s: Old %2$s -> New %3$s | Changelog: %4$s', 'rank-math' ), $product_data['name'], $product_data['old_version'], $product_data['new_version'], $product_data['changelog'] ) . "\n";
		}

		return $list;
	}
}
