<?php
/**
 * Shortcode - Job Posting
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
		esc_html__( 'Salary', 'rank-math' ),
		'baseSalary.value.value'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Salary Currency', 'rank-math' ),
		'baseSalary.currency'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Payroll', 'rank-math' ),
		'baseSalary.value.unitText'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Date Posted', 'rank-math' ),
		'datePosted'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Expiry Posted', 'rank-math' ),
		'validThrough'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Unpublish when expired', 'rank-math' ),
		'unpublish'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Employment Type ', 'rank-math' ),
		'employmentType'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Hiring Organization ', 'rank-math' ),
		'hiringOrganization.name'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Organization URL', 'rank-math' ),
		'hiringOrganization.sameAs'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Organization Logo', 'rank-math' ),
		'hiringOrganization.logo'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Location', 'rank-math' ),
		'jobLocation.address'
	);
	?>

</div>
