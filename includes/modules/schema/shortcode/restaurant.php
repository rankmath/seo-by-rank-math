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
		esc_html__( 'Address', 'seo-by-rank-math' ),
		'address'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Geo Coordinates', 'seo-by-rank-math' ),
		'geo'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Phone Number', 'seo-by-rank-math' ),
		'telephone'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Price Range', 'seo-by-rank-math' ),
		'priceRange'
	);
	?>

	<?php $this->get_opening_hours( 'openingHoursSpecification' ); ?>

	<?php
	$this->get_field(
		esc_html__( 'Serves Cuisine', 'seo-by-rank-math' ),
		'servesCuisine'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Menu URL', 'seo-by-rank-math' ),
		'hasMenu'
	);
	?>

</div>
