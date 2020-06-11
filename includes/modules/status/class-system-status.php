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

use MyThemeShop\Helpers\WordPress as WordPress_Helper;
use MyThemeShop\Helpers\Str;

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
		( new Error_Log )->display();
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
					<span class="success" aria-hidden="true"><?php esc_html_e( 'Copied!', 'rank-math' ); ?></span>
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

		$tables        = [];
		$db_index_size = 0;
		$db_data_size  = 0;

		$database_table_information = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				table_name AS 'name',
				engine AS 'engine',
				round( ( data_length / 1024 / 1024 ), 2 ) 'data',
				round( ( index_length / 1024 / 1024 ), 2 ) 'index'
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND table_name LIKE %s
				ORDER BY name ASC;",
				DB_NAME,
				'%rank_math%'
			)
		);

		$tables = [];
		foreach ( $database_table_information as $table ) {
			$tables[ $table->name ] = [
				'data'   => $table->data,
				'index'  => $table->index,
				'engine' => $table->engine,
			];

			$db_data_size  += $table->data;
			$db_index_size += $table->index;
		}

		$this->info = [
			'database_version' => get_option( 'rank_math_db_version' ),
			'table_prefix'     => $wpdb->prefix,
			'tables'           => $tables,
			'data_size'        => $db_data_size . 'MB',
			'index_size'       => $db_index_size . 'MB',
			'total_size'       => $db_data_size + $db_index_size . 'MB',
		];

		// Core debug data.
		if ( ! class_exists( 'WP_Debug_Data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php';
		}
		wp_enqueue_style( 'site-health' );
		wp_enqueue_script( 'site-health' );
		$this->wp_info = \WP_Debug_Data::debug_data();
		unset( $this->wp_info['wp-paths-sizes'] );
	}
}
