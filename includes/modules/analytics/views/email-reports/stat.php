<?php
/**
 * Analytics Report header template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

$diff_class = 'n/a' !== $diff ? ( $diff > 0 ? 'positive' : 'negative' ) : 'n/a';

if ( ! empty( $invert ) ) {
	$diff_class = 'n/a' !== $diff ? ( $diff < 0 ? 'positive' : 'negative' ) : 'n/a';
}

$diff_sign = '<span class="diff-sign">' . ( 'positive' === $diff_class ? '&#9650;' : '&#9660;' ) . '</span>';

if ( 0.0 === floatval( $diff ) ) {
	$diff_class = 'no-diff';
	$diff_sign  = '';
}

$stat_value = $value;
$stat_diff  = 'n/a' !== $diff ? abs( $diff ) : 'n/a';

// Human number is 'true' by default.
if ( ! isset( $human_number ) || $human_number ) {
	$stat_value = Str::human_number( $stat_value );
	$stat_diff  = Str::human_number( $stat_diff );
}

?>
<span class="stat-value">
	<?php echo esc_html( $stat_value ); ?>
</span>
<span class="stat-diff <?php echo sanitize_html_class( $diff_class ); ?>">
	<?php echo $diff_sign . ' ' . esc_html( $stat_diff ); // phpcs:ignore ?>
</span>

<?php
if ( ! empty( $graph ) && ! empty( $graph_data ) ) {

	$show_graph = false;
	// Check data points.
	foreach ( $graph_data as $key => $value ) {
		if ( ! empty( $value ) ) {
			$show_graph = true;
		}

		// Adjust values.
		if ( ! empty( $graph_modifier ) ) {
			$graph_data[ $key ] = abs( $graph_data[ $key ] + $graph_modifier );
		}
	}

	if ( ! $show_graph ) {
		return;
	}

	// `img` tag size.
	// Actual image size is 3x this.
	$width  = 64;
	$height = 34;

	$this->image( $this->charts_api_url( $graph_data, $width * 3, $height * 3 ), $width, $height, __( 'Data Chart', 'rank-math' ), [ 'style' => 'float: right;margin-top: -7px;' ] );
} ?>
