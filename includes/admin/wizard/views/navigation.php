<?php
/**
 * Setup wizard navigation template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

$output_steps = $this->steps;
$array_keys   = array_keys( $this->steps );
$current_step = array_search( $this->step, $array_keys, true );
?>
<div class="wizard-navigation">

	<a class="step step-label" href="<?php echo esc_url( apply_filters( 'rank_math/wizard/step/label_url', \RankMath\Helper::get_admin_url( 'wizard' ) ) ); ?>" title="<?php echo apply_filters( 'rank_math/wizard/step/label', esc_html__( 'Getting Started', 'rank-math' ) ); ?>"></a>

	<?php
	foreach ( $output_steps as $step_key => $step ) :

		if ( $this->is_nav_item_hidden( $step_key ) ) {
			continue;
		}

		$class_attr = '';

		if ( $step_key === $this->step ) {
			$class_attr = 'active';
		} elseif ( $current_step > array_search( $step_key, $array_keys, true ) ) {
			$class_attr = 'done';
		}
		?>

		<a class="<?php echo esc_attr( $class_attr ); ?>" href="<?php echo esc_url( $this->get_step_link( $step_key ) ); ?>" title="<?php echo esc_attr( $step['name'] ); ?>"><span></span></a>

	<?php endforeach; ?>

</div>
