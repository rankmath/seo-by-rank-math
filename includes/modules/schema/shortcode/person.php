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
		esc_html__( 'Email', 'seo-by-rank-math' ),
		'email'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Address', 'seo-by-rank-math' ),
		'address'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Gender', 'seo-by-rank-math' ),
		'gender'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Job Title', 'seo-by-rank-math' ),
		'jobTitle'
	);
	?>

</div>
