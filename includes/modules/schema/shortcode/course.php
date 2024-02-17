<?php
/**
 * Shortcode - Course
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
		esc_html__( 'Course Provider', 'rank-math' ),
		'provider.@type'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Provider Name', 'rank-math' ),
		'provider.name'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Provider URL', 'rank-math' ),
		'provider.sameAs'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Mode', 'rank-math' ),
		'hasCourseInstance.courseMode'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Workload', 'rank-math' ),
		'hasCourseInstance.courseWorkload',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Start Date', 'rank-math' ),
		'hasCourseInstance.courseSchedule.startDate',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'End Date', 'rank-math' ),
		'hasCourseInstance.courseSchedule.endDate',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Duration', 'rank-math' ),
		'hasCourseInstance.courseSchedule.duration',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Repeat Count', 'rank-math' ),
		'hasCourseInstance.courseSchedule.repeatCount',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Repeat Frequency', 'rank-math' ),
		'hasCourseInstance.courseSchedule.repeatFrequency',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Type', 'rank-math' ),
		'offers.category'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Currency', 'rank-math' ),
		'offers.priceCurrency'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Price', 'rank-math' ),
		'offers.price'
	);
	?>

	<?php $this->show_ratings(); ?>

</div>
