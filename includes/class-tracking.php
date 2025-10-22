<?php
/**
 * Code related to the Mixpanel Tracking.
 *
 * @since      1.0.x
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

declare(strict_types=1);

namespace RankMath;

use RankMath\Traits\Hooker;
use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Helpers\Str;
use RankMath\Helpers\Editor;
use RankMath\Admin\Admin_Helper;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin;

/**
 * Tracking class.
 */
class Tracking {
	use Hooker;

	/**
	 * Opt-in instance.
	 *
	 * @var Optin
	 */
	private $optin;

	/**
	 * Mixpanel instance.
	 *
	 * @var TrackingPlugin
	 */
	private $mixpanel;

	/**
	 * User email for identification.
	 *
	 * @var string
	 */
	private $user_email = '';

	/**
	 * User language.
	 *
	 * @var string
	 */
	private $user_language = '';

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	private $plugin = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->plugin   = defined( 'RANK_MATH_PRO_VERSION' ) ? 'Rank Math Pro ' . RANK_MATH_PRO_VERSION : 'Rank Math Free ' . rank_math()->version;
		$this->optin    = new Optin( 'rank_math', 'manage_options' );
		$this->mixpanel = new TrackingPlugin( '517e881edc2636e99a2ecf013d8134d3', $this->plugin, 'RankMath', 'RankMath' );

		$this->action( 'init', 'hooks' );
	}

	/**
	 * Initialize the tracking class hooks.
	 */
	public function hooks(): void {
		$this->init_user_data();
		$this->action( 'rank_math_mixpanel_optin_changed', 'track_optin_change' );
		$this->action( 'rank_math/module_changed', 'track_module_option_change', 10, 2 );
		$this->action( 'rank_math/setup_wizard/enable_tracking', 'enable_usage_tracking' );
		$this->action( 'rank_math/setup_wizard/step_viewed', 'track_setup_wizard_step_view' );
		$this->action( 'rank_math/admin/enqueue_scripts', 'enqueue_mixpanel' );
		$this->action( 'admin_init', 'track_admin_page_view' );
		$this->action( 'rank_math/admin/options/general_data', 'set_usage_tracking_option', 99 );
		$this->action( 'rank_math/settings/before_save', 'update_mixpanel_optin', 10, 2 );
		$this->filter( 'rank_math/settings/saved_data', 'add_mixpanel_data', 10, 2 );
		$this->action( 'cmb2_save_options-page_fields_rank-math-options-general_options', 'update_mixpanel_optin_cmb2' );
	}

	/**
	 * Track the opt-in status change.
	 *
	 * @param bool $status The new opt-in status.
	 */
	public function track_optin_change( $status ): void {
		$this->identify_user();
		$this->mixpanel->track_optin( $status );
	}

	/**
	 * Track module option changes.
	 *
	 * @param string $module_id The module ID.
	 * @param string $state     The new state (on/off).
	 */
	public function track_module_option_change( $module_id, $state ): void {
		if ( ! $this->is_opted_in() ) {
			return;
		}

		$enable_module = $state === 'on';
		$properties    = [
			'context'        => 'wp_plugin',
			'option_name'    => 'module ' . $module_id,
			'previous_value' => ! $enable_module ? 1 : 0,
			'new_value'      => $enable_module ? 1 : 0,
			'language'       => $this->user_language,
		];

		$this->track_event( 'Option Changed', $properties );
	}

	/**
	 * Enable or disable usage tracking.
	 *
	 * @param bool $enable True to enable, false to disable.
	 */
	public function enable_usage_tracking( $enable ): void {
		if ( $enable ) {
			$this->optin->enable();
			return;
		}

		$this->optin->disable();
	}

	/**
	 * Track setup wizard step views.
	 */
	public function track_setup_wizard_step_view() {
		if ( ! $this->is_opted_in() ) {
			return;
		}

		// Get the actual admin page URL from the request referer.
		$referer     = wp_get_referer();
		$current_url = $referer ? $referer : Helper::get_current_page_url();
		$path        = $this->get_current_path_with_query();

		// Parse the referer URL to get the current step being viewed.
		$url_parts = wp_parse_url( $referer );
		parse_str( $url_parts['query'] ?? '', $query_params );
		$current_step_being_viewed = $query_params['step'] ?? 'compatibility';

		// Use static variable to prevent duplicate tracking in the same request.
		static $tracked_steps = [];
		if ( in_array( $current_step_being_viewed, $tracked_steps, true ) ) {
			return;
		}
		$tracked_steps[] = $current_step_being_viewed;

		$properties = [
			'current_url' => $current_url,
			'path'        => $path,
			'context'     => 'wp_plugin',
			'language'    => $this->user_language,
		];

		$this->track_event( 'Page Viewed', $properties );
	}

	/**
	 * Enqueue Mixpanel script on Block Editor pages.
	 */
	public function enqueue_mixpanel(): void {
		if ( ! $this->optin->can_track() ) {
			return;
		}

		if ( ! Helper::is_block_editor() || ! Editor::can_add_editor() ) {
			return;
		}

		$this->mixpanel->add_script();
		Helper::add_json(
			'tracking',
			[
				'plugin'   => $this->plugin,
				'path'     => isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
				'email'    => $this->user_email,
				'language' => $this->user_language,
			],
		);
	}

	/**
	 * Track admin page views.
	 */
	public function track_admin_page_view() {
		if ( ! $this->optin->can_track() ) {
			return;
		}

		// Only track Rank Math admin/configuration pages.
		if ( ! Str::starts_with( 'rank-math', Param::get( 'page' ) ) ) {
			return;
		}

		// Check if we've already tracked this user in the last 7 days.
		$transient_key = 'rank_math_admin_tracking_' . md5( $this->user_email );
		if ( get_transient( $transient_key ) ) {
			return;
		}

		// Set transient for 7 days to prevent duplicate tracking.
		set_transient( $transient_key, true, WEEK_IN_SECONDS );

		// Get the current admin page URL.
		$current_url = Helper::get_current_page_url();
		$path        = $this->get_current_path_with_query();

		$properties = [
			'current_url' => $current_url,
			'path'        => $path,
			'context'     => 'wp_plugin',
			'language'    => $this->user_language,
		];

		// Determine capability based on current Rank Math page and pass it to Mixpanel.
		$page             = (string) Param::get( 'page' );
		$event_capability = $this->get_event_capability_for_page( $page );
		$this->track_event( 'Page Viewed', $properties, $event_capability );
	}

	/**
	 * Add usage_tracking option to the general settings.
	 *
	 * @param array $json Localized data.
	 */
	public function set_usage_tracking_option( $json ) {
		// Early bail if the current page is not general settings.
		if ( ! isset( $json['optionPage'] ) || $json['optionPage'] !== 'general' || ! isset( $json['data'] ) ) {
			return $json;
		}

		$json['canAddUsageTracking']    = current_user_can( 'manage_options' );
		$json['data']['usage_tracking'] = $this->optin->can_track();

		return $json;
	}

	/**
	 * Update opt-in value.
	 *
	 * @param string $type     Settings type.
	 * @param array  $settings Settings data.
	 */
	public function update_mixpanel_optin( $type, $settings ) {
		if ( $type !== 'general' || ! isset( $settings['usage_tracking'] ) ) {
			return;
		}

		if ( ! empty( $settings['usage_tracking'] ) ) {
			$this->optin->enable();
			return;
		}

		$this->optin->disable();
	}

	/**
	 * Add usage tracking data to the saved settings.
	 *
	 * @param array  $data Settings data.
	 * @param string $type Settings type.
	 * @return array
	 */
	public function add_mixpanel_data( $data, $type ) {
		if ( $type !== 'general' ) {
			return $data;
		}

		$data['usage_tracking'] = $this->optin->can_track();
		return $data;
	}

	/**
	 * Update Mixpanel optin option when general settings are saved. Used
	 */
	public function update_mixpanel_optin_cmb2() {
		// Get the value from the form submission.
		$usage_tracking = isset( $_POST['usage_tracking'] ) ? sanitize_text_field( wp_unslash( $_POST['usage_tracking'] ) ) : 'off';
		if ( $usage_tracking === 'on' ) {
			$this->optin->enable();
			return;
		}

		$this->optin->disable();
	}

	/**
	 * Check if usage tracking is enabled (opt-in).
	 *
	 * @return bool
	 */
	public function is_opted_in(): bool {
		return $this->optin->is_enabled();
	}

	/**
	 * Track a custom event for Rank Math.
	 *
	 * @param string $event            Event name.
	 * @param array  $properties       Additional properties to merge.
	 * @param string $event_capability The capability required to track the event.
	 */
	public function track_event( string $event, array $properties = [], string $event_capability = '' ): void {
		$defaults = [
			'context'  => 'wp_plugin',
			'language' => $this->user_language,
		];

		$this->identify_user();
		$this->mixpanel->track( $event, array_merge( $properties, $defaults ), $event_capability );
	}

	/**
	 * Get the current request path with query string, suitable for tracking.
	 * Handles AJAX and REST requests by using referer when available.
	 *
	 * @return string
	 */
	public function get_current_path_with_query(): string {
		// For AJAX/REST requests, use referer to get the originating page.
		$referer     = wp_get_referer();
		$current_url = $referer ? $referer : Helper::get_current_page_url();
		$path        = wp_parse_url( $current_url, PHP_URL_PATH ) . '?' . wp_parse_url( $current_url, PHP_URL_QUERY );

		return $path;
	}

	/**
	 * Identify the current user in Mixpanel.
	 * Safe to call multiple times; no-ops when opt-out.
	 */
	public function identify_user(): void {
		$this->mixpanel->identify( $this->user_email );
	}

	/**
	 * Get the current plugin label (with version) used in tracking payloads.
	 *
	 * @return string
	 */
	public function get_plugin_label(): string {
		return $this->plugin;
	}

	/**
	 * Initialize user data.
	 */
	private function init_user_data() {
		if ( ! $this->user_email ) {
			$this->user_email = $this->get_user_email();
		}

		if ( ! $this->user_language ) {
			$this->user_language = get_user_locale();
		}
	}

	/**
	 * Get user email for identification.
	 *
	 * @return string
	 */
	private function get_user_email(): string {
		$account = Admin_Helper::get_registration_data();
		if ( ! empty( $account['email'] ) ) {
			return $account['email'];
		}

		$user = wp_get_current_user();
		return isset( $user->user_email ) ? (string) $user->user_email : '';
	}

	/**
	 * Get the capability required for tracking based on the current Rank Math page.
	 *
	 * @param string $page The `page` query arg, e.g., 'rank-math-options-general'.
	 *
	 * @return string Capability name or empty for default behavior.
	 */
	private function get_event_capability_for_page( string $page ): string {
		// Map known Rank Math admin pages to their capabilities.
		$map = [
			'rank-math-options-general'          => 'rank_math_general',
			'rank-math-options-titles'           => 'rank_math_titles',
			'rank-math-options-sitemap'          => 'rank_math_sitemap',
			'rank-math-options-instant-indexing' => 'rank_math_general',
			'rank-math-404-monitor'              => 'rank_math_404_monitor',
			'rank-math-redirections'             => 'rank_math_redirections',
			'rank-math-role-manager'             => 'rank_math_role_manager',
			'rank-math-analytics'                => 'rank_math_analytics',
			'rank-math-seo-analysis'             => 'rank_math_site_analysis',
			'rank-math-content-ai-page'          => 'rank_math_content_ai',
		];

		return isset( $map[ $page ] ) ? $map[ $page ] : '';
	}
}
