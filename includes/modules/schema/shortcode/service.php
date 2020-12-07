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
		esc_html__( 'Service Type', 'rank-math' ),
		'serviceType'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Price', 'rank-math' ),
		'offers.price'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Currency', 'rank-math' ),
		'offers.priceCurrency'
	);
	?>

</div>
