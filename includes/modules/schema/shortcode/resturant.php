<?php
/**
 * Shortcode - resturant
 *
 * @package    RankMath
 * @subpackage RankMath\Schema
 */

$this->get_title();
$this->get_image();
?>
<div class="rank-math-review-data">

	<?php $this->get_description(); ?>

	<?php
	$this->get_field(
		esc_html__( 'Address', 'rank-math' ),
		'address'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Geo Coordinates', 'rank-math' ),
		'geo'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Phone Number', 'rank-math' ),
		'telephone'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Price Range', 'rank-math' ),
		'priceRange'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Opening Time', 'rank-math' ),
		'openingHoursSpecification.opens'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Closing Time', 'rank-math' ),
		'openingHoursSpecification.closes'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Open Days', 'rank-math' ),
		'openingHoursSpecification.dayOfWeek'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Serves Cuisine', 'rank-math' ),
		'servesCuisine'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Menu URL', 'rank-math' ),
		'hasMenu'
	);
	?>

</div>
