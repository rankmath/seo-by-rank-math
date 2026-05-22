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
		esc_html__( 'Price', 'seo-by-rank-math' ),
		'offers.price'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Price Currency', 'seo-by-rank-math' ),
		'offers.priceCurrency'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Operating System', 'seo-by-rank-math' ),
		'operatingSystem'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Application Category', 'seo-by-rank-math' ),
		'applicationCategory'
	);
	?>

	<?php $this->show_ratings(); ?>

</div>
