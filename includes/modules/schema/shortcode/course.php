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

	<?php $this->show_ratings(); ?>

</div>
