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
		esc_html__( 'Course Provider', 'seo-by-rank-math' ),
		'provider.@type'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Provider Name', 'seo-by-rank-math' ),
		'provider.name'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Provider URL', 'seo-by-rank-math' ),
		'provider.sameAs'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Mode', 'seo-by-rank-math' ),
		'hasCourseInstance.courseMode'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Workload', 'seo-by-rank-math' ),
		'hasCourseInstance.courseWorkload',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Start Date', 'seo-by-rank-math' ),
		'hasCourseInstance.courseSchedule.startDate',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'End Date', 'seo-by-rank-math' ),
		'hasCourseInstance.courseSchedule.endDate',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Duration', 'seo-by-rank-math' ),
		'hasCourseInstance.courseSchedule.duration',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Repeat Count', 'seo-by-rank-math' ),
		'hasCourseInstance.courseSchedule.repeatCount',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Repeat Frequency', 'seo-by-rank-math' ),
		'hasCourseInstance.courseSchedule.repeatFrequency',
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Type', 'seo-by-rank-math' ),
		'offers.category'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Currency', 'seo-by-rank-math' ),
		'offers.priceCurrency'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Course Price', 'seo-by-rank-math' ),
		'offers.price'
	);
	?>

	<?php $this->show_ratings(); ?>

</div>
