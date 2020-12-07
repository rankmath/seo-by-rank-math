<?php
/**
 * Shortcode - Restaurant
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

	<?php $this->get_opening_hours( 'openingHoursSpecification' ); ?>

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
