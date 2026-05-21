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
		esc_html__( 'Salary', 'seo-by-rank-math' ),
		'baseSalary.value.value'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Salary Currency', 'seo-by-rank-math' ),
		'baseSalary.currency'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Payroll', 'seo-by-rank-math' ),
		'baseSalary.value.unitText'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Date Posted', 'seo-by-rank-math' ),
		'datePosted'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Expiry Posted', 'seo-by-rank-math' ),
		'validThrough'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Unpublish when expired', 'seo-by-rank-math' ),
		'unpublish'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Employment Type ', 'seo-by-rank-math' ),
		'employmentType'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Hiring Organization ', 'seo-by-rank-math' ),
		'hiringOrganization.name'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Organization URL', 'seo-by-rank-math' ),
		'hiringOrganization.sameAs'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Organization Logo', 'seo-by-rank-math' ),
		'hiringOrganization.logo'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Location', 'seo-by-rank-math' ),
		'jobLocation.address'
	);
	?>

</div>
