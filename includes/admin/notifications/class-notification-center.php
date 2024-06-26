<?php
/**
 * The Notification center handles notifications storage and display.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath\Admin\Notifications;

/**
 * Notification_Center class.
 */
class Notification_Center {

	/**
	 * Option name to store notifications in.
	 *
	 * @var string
	 */
	private $storage_key = '';

	/**
	 * Notifications.
	 *
	 * @var array
	 */
	private $notifications = [];

	/**
	 * Stores whether we need to clear storage or not.
	 *
	 * @var array
	 */
	private $should_clear_storage = true;

	/**
	 * Stores already displayed notice texts to avoid duplication.
	 *
	 * @var array
	 */
	private $displayed_notifications = [];

	/**
	 * Internal flag for whether notifications have been retrieved from storage.
	 *
	 * @var bool
	 */
	private $retrieved = false;

	/**
	 * Construct
	 *
	 * @param string $storage_key Option name to store notification in.
	 */
	public function __construct( $storage_key = 'mythemeshop_notifications' ) {
		$this->storage_key = $storage_key;
		add_action( 'plugins_loaded', [ $this, 'get_from_storage' ], 5 );
		add_action( 'all_admin_notices', [ $this, 'display' ] );
		add_action( 'shutdown', [ $this, 'update_storage' ] );
		add_action( 'admin_footer', [ $this, 'print_javascript' ] );

		add_action( 'wp_ajax_wp_helpers_notice_dismissible', [ $this, 'notice_dismissible' ] );
	}

	/**
	 * Retrieve the notifications from storage
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array Notification[] Notifications
	 */
	public function get_from_storage() {
		if ( $this->retrieved ) {
			return;
		}

		$this->retrieved = true;
		$notifications   = get_option( $this->storage_key );

		// Check if notifications are stored.
		if ( empty( $notifications ) ) {
			$this->should_clear_storage = false;
			return;
		}

		if ( is_array( $notifications ) ) {
			foreach ( $notifications as $notification ) {
				$this->notifications[] = new Notification(
					$notification['message'],
					$notification['options']
				);
			}
		}
	}

	/**
	 * Display the notifications.
	 *
	 * @codeCoverageIgnore
	 */
	public function display() {

		// Never display notifications for network admin.
		if ( $this->is_network_admin() ) {
			return;
		}

		$notifications = $this->get_sorted_notifications();
		if ( empty( $notifications ) ) {
			return;
		}

		foreach ( $notifications as $notification ) {
			if ( $notification->can_display() && ! in_array( (string) $notification, $this->displayed_notifications, true ) ) {
				echo wp_kses_post( $notification );
				$this->displayed_notifications[] = (string) $notification;
			}
		}
	}

	/**
	 * Print JS for dismissile.
	 *
	 * @codeCoverageIgnore
	 */
	public function print_javascript() {
		?>
		<script>
			;(function($) {
				$( '.wp-helpers-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
					var notice = $( this ).parent()

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'wp_helpers_notice_dismissible',
							security: notice.data( 'security' ),
							notificationId: notice.attr( 'id' )
						}
					});
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Save persistent or transactional notifications to storage.
	 *
	 * We need to be able to retrieve these so they can be dismissed at any time during the execution.
	 *
	 * @codeCoverageIgnore
	 */
	public function update_storage() {
		$notifications = $this->get_notifications();
		$notifications = array_filter( $notifications, [ $this, 'remove_notification' ] );

		/**
		 * Filter: 'wp_helpers_notifications_before_storage' - Allows developer to filter notifications before saving them.
		 *
		 * @param Notification[] $notifications
		 */
		$notifications = apply_filters( 'wp_helpers_notifications_before_storage', $notifications );

		// No notifications to store, clear storage.
		if ( empty( $notifications ) && $this->should_clear_storage ) {
			delete_option( $this->storage_key );
			return;
		}

		$notifications = array_map( [ $this, 'notification_to_array' ], $notifications );

		// Save the notifications to the storage.
		update_option( $this->storage_key, $notifications );
	}

	/**
	 * Dismiss persistent notice.
	 *
	 * @codeCoverageIgnore
	 */
	public function notice_dismissible() {
		$notification_id = filter_input( INPUT_POST, 'notificationId' );
		check_ajax_referer( $notification_id, 'security' );

		$notification = $this->remove_by_id( $notification_id );

		/**
		 * Filter: 'wp_helpers_notification_dismissed' - Allows developer to perform action after dismissed.
		 *
		 * @param Notification[] $notifications
		 */
		do_action( 'wp_helpers_notification_dismissed', $notification_id, $notification );
	}

	/**
	 * Add notification
	 *
	 * @param string $message Message string.
	 * @param array  $options Set of options.
	 */
	public function add( $message, $options = [] ) {
		if ( isset( $options['id'] ) && ! is_null( $this->get_notification_by_id( $options['id'] ) ) ) {
			return;
		}

		$this->notifications[] = new Notification(
			$message,
			$options
		);
	}

	/**
	 * Provide a way to verify present notifications
	 *
	 * @return array|Notification[] Registered notifications.
	 */
	public function get_notifications() {
		return $this->notifications;
	}

	/**
	 * Get the notification by ID
	 *
	 * @param  string $notification_id The ID of the notification to search for.
	 * @return null|Notification
	 */
	public function get_notification_by_id( $notification_id ) {
		foreach ( $this->notifications as $notification ) {
			if ( $notification_id === $notification->args( 'id' ) ) {
				return $notification;
			}
		}
		return null;
	}

	/**
	 * Remove the notification by ID
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $notification_id The ID of the notification to search for.
	 * @return Notification Instance of delete notification.
	 */
	public function remove_by_id( $notification_id ) {
		$notification = $this->get_notification_by_id( $notification_id );
		if ( ! is_null( $notification ) ) {
			$notification->dismiss();
		}

		return $notification;
	}

	/**
	 * Remove notification after it has been displayed.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param Notification $notification Notification to remove.
	 */
	public function remove_notification( Notification $notification ) {
		if ( ! $notification->is_displayed() ) {
			return true;
		}

		if ( $notification->is_persistent() ) {
			return true;
		}

		return false;
	}

	/**
	 * Return the notifications sorted on type and priority
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array|Notification[] Sorted Notifications
	 */
	private function get_sorted_notifications() {
		$notifications = $this->get_notifications();
		if ( empty( $notifications ) ) {
			return [];
		}

		// Sort by severity, error first.
		usort( $notifications, [ $this, 'sort_notifications' ] );

		return $notifications;
	}

	/**
	 * Sort on type then priority
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  Notification $first  Compare with B.
	 * @param  Notification $second Compare with A.
	 * @return int 1, 0 or -1 for sorting offset.
	 */
	private function sort_notifications( Notification $first, Notification $second ) {

		if ( 'error' === $first->args( 'type' ) ) {
			return -1;
		}

		if ( 'error' === $second->args( 'type' ) ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Convert Notification to array representation
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  Notification $notification Notification to convert.
	 * @return array
	 */
	private function notification_to_array( Notification $notification ) {
		return $notification->to_array();
	}

	/**
	 * Check if is network admin.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return bool
	 */
	private function is_network_admin() {
		return function_exists( 'is_network_admin' ) && is_network_admin();
	}

	/**
	 * Check if a notification with the given ID exists.
	 *
	 * @param string $id Notification ID.
	 * @return boolean
	 */
	public function has_notification( $id ) {
		$notifications = $this->get_notifications();
		foreach ( $notifications as $notification ) {
			if ( isset( $notification->options['id'] ) && $notification->options['id'] === $id ) {
				return true;
			}
		}
		return false;
	}
}
