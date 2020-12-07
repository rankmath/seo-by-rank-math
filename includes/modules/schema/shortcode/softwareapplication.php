<?php
/**
 * Shortcode - Software Application
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
		esc_html__( 'Price', 'rank-math' ),
		'offers.price'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Price Currency', 'rank-math' ),
		'offers.priceCurrency'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Operating System', 'rank-math' ),
		'operatingSystem'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Application Category', 'rank-math' ),
		'applicationCategory'
	);
	?>

</div>
