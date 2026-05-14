<?php
/**
 * Shortcode - Service
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
		esc_html__( 'Service Type', 'seo-by-rank-math' ),
		'serviceType'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Price', 'seo-by-rank-math' ),
		'offers.price'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Currency', 'seo-by-rank-math' ),
		'offers.priceCurrency'
	);
	?>

</div>
