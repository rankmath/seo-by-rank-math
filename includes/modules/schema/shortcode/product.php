<?php
/**
 * Shortcode - Product
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
		esc_html__( 'Product SKU', 'seo-by-rank-math' ),
		'sku'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Product Brand', 'seo-by-rank-math' ),
		'brand.name'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Product Currency', 'seo-by-rank-math' ),
		'offers.priceCurrency'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Product Price', 'seo-by-rank-math' ),
		'offers.price'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Price Valid Until', 'seo-by-rank-math' ),
		'offers.priceValidUntil'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Product In-Stock', 'seo-by-rank-math' ),
		'offers.availability'
	);
	?>

	<?php $this->show_ratings(); ?>

</div>
