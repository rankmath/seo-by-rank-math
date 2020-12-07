<?php
/**
 * Shortcode - Person
 *
 * @package    RankMath
 * @subpackage RankMath\Schema
 */

defined( 'ABSPATH' ) || exit;

$this->get_title();
$this->get_image();
?>
<div class="rank-math-review-data">

	<?php $this->get_description(); ?>

	<?php
	$this->get_field(
		esc_html__( 'Email', 'rank-math' ),
		'email'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Address', 'rank-math' ),
		'address'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Gender', 'rank-math' ),
		'gender'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Job Title', 'rank-math' ),
		'jobTitle'
	);
	?>

</div>
