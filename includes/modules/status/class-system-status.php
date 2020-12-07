<?php
/**
 * The System_Status Class.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Status;

use RankMath\Google\Authentication;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * System_Status class.
 */
class System_Status {

	/**
	 * Display Database/Tables Details.
	 */
	public function display() {
		$this->prepare_info();

		$this->display_system_info();
		( new Error_Log() )->display();
	}

	/**
	 * Display system details.
	 */
	private function display_system_info() {
		?>
		<div class="rank-math-system-status rank-math-box">
			<header>
				<h3><?php esc_html_e( 'System Info', 'rank-math' ); ?></h3>
			</header>

			<div class="site-health-copy-buttons">
				<div class="copy-button-wrapper">
					<button type="button" class="button copy-button" data-clipboard-text="<?php echo esc_attr( \WP_Debug_Data::format( $this->wp_info, 'debug' ) ); ?>">
						<?php esc_html_e( 'Copy System Info to Clipboard', 'rank-math' ); ?>
					</button>
					<span class="success hidden" aria-hidden="true"><?php esc_html_e( 'Copied!', 'rank-math' ); ?></span>
				</div>
			</div>

			<div id="health-check-debug" class="health-check-accordion">
				<?php $this->display_system_info_list(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display list for system info.
	 *
	 * @return void
	 */
	private function display_system_info_list() {
		$directory = dirname( __FILE__ );
		foreach ( $this->wp_info as $section => $details ) {
			if ( ! isset( $details['fields'] ) || empty( $details['fields'] ) ) {
				continue;
			}

			include( $directory . '/views/system-status-accordion.php' );
		}
	}

	/**
	 * Display individual fields for the system info.
	 *
	 * @param  array $fields Fields array.
	 * @return void
	 */
	private function display_system_info_fields( $fields ) {
		foreach ( $fields as $field_name => $field ) {
			$values = $this->system_info_value( $field_name, $field['value'] );
			printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( $field['label'] ), $values );
		}
	}

	/**
	 * Get individual values for the system info.
	 *
	 * @param  string $field_name  Field name.
	 * @param  mixed  $field_value Field value.
	 * @return string              Output HTML.
	 */
	private function system_info_value( $field_name, $field_value ) {
		if ( is_array( $field_value ) ) {
			$values = '<ul>';
			foreach ( $field_value as $name => $value ) {
				$values .= sprintf( '<li>%s: %s</li>', esc_html( $name ), esc_html( $value ) );
			}
			$values .= '</ul>';

			return $values;
		}

		return esc_html( $field_value );
	}

	/**
	 * Get Database information.
	 */
	private function prepare_info() {
		global $wpdb;

		$plan   = Admin_Helper::get_registration_data();
		$tokens = Authentication::tokens();

		$rankmath = [
			'label'  => esc_html__( 'Rank Math', 'rank-math' ),
			'fields' => [
				'version' => [
					'label' => esc_html__( 'Version', 'rank-math' ),
					'value' => get_option( 'rank_math_version' ),
				],
				'database_version' => [
					'label' => esc_html__( 'Database version', 'rank-math' ),
					'value' => get_option( 'rank_math_db_version' ),
				],
				'plugin_plan' => [
					'label' => esc_html__( 'Plugin subscription plan', 'rank-math' ),
					'value' => isset( $plan['plan'] ) ? \ucwords( $plan['plan'] ) : esc_html__( 'Free', 'rank-math' ),
				],
				'refresh_token' => [
					'label' => esc_html__( 'Google Refresh token', 'rank-math' ),
					'value' => empty( $tokens['refresh_token'] ) ? esc_html__( 'No token', 'rank-math' ) : esc_html__( 'Token exists', 'rank-math' ),
				],
			],
		];

		$database_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				table_name AS 'name'
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND table_name LIKE %s
				ORDER BY name ASC;",
				DB_NAME,
				'%rank_math%'
			)
		);

		$tables = [];
		foreach ( $database_tables as $table ) {
			$name = \str_replace( $wpdb->prefix, '', $table->name );
			$tables[ $name ] = true;
		}

		$should_exists = [
			'rank_math_404_logs'                  => esc_html__( 'Database Table: 404 Log', 'rank-math' ),
			'rank_math_redirections'              => esc_html__( 'Database Table: Redirection', 'rank-math' ),
			'rank_math_redirections_cache'        => esc_html__( 'Database Table: Redirection Cache', 'rank-math' ),
			'rank_math_internal_links'            => esc_html__( 'Database Table: Internal Link', 'rank-math' ),
			'rank_math_internal_meta'             => esc_html__( 'Database Table: Internal Link Meta', 'rank-math' ),
			'rank_math_analytics_gsc'             => esc_html__( 'Database Table: Google Search Console', 'rank-math' ),
			'rank_math_analytics_objects'         => esc_html__( 'Database Table: Flat Posts', 'rank-math' ),
			'rank_math_analytics_ga'              => esc_html__( 'Database Table: Google Analytics', 'rank-math' ),
			'rank_math_analytics_adsense'         => esc_html__( 'Database Table: Google AdSense', 'rank-math' ),
			'rank_math_analytics_keyword_manager' => esc_html__( 'Database Table: Keyword Manager', 'rank-math' ),
		];

		foreach ( $should_exists as $name => $label ) {
			$rankmath['fields'][ $name ] = [
				'label' => $label,
				'value' => isset( $tables[ $name ] ) ? esc_html__( 'Created', 'rank-math' ) : esc_html__( 'Doesn\'t exists', 'rank-math' ),
			];
		}

		// Core debug data.
		if ( ! class_exists( 'WP_Debug_Data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
		}
		wp_enqueue_style( 'site-health' );
		wp_enqueue_script( 'site-health' );
		$this->wp_info = [ 'rank-math' => $rankmath ] + \WP_Debug_Data::debug_data();
		unset( $this->wp_info['wp-paths-sizes'] );
	}
}
