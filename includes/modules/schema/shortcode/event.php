<?php
/**
 * Shortcode - Event
 *
 * @package    RankMath
 * @subpackage RankMath\Schema
 */

defined( 'ABSPATH' ) || exit;

$this->get_title();
$this->get_image();

$value      = $this->get_field_value( 'eventAttendanceMode' );
$is_online  = 'Online' === $value;
$is_offline = 'Offline' === $value;

if ( 'MixedEventAttendanceMode' === $value ) {
	$is_online  = true;
	$is_offline = true;
	$value      = esc_html__( 'Online + Offline', 'seo-by-rank-math' );
}
?>
<div class="rank-math-review-data">

	<?php $this->get_description(); ?>

	<?php
	$this->get_field(
		esc_html__( 'Event Type', 'seo-by-rank-math' ),
		'@type'
	);
	?>

	<?php
	$this->output_field(
		esc_html__( 'Event Attendance Mode', 'seo-by-rank-math' ),
		$value
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Event Status', 'seo-by-rank-math' ),
		'eventStatus'
	);
	?>

	<?php
	if ( $is_offline ) {
		$this->get_field(
			esc_html__( 'Venue Name', 'seo-by-rank-math' ),
			'location.name'
		);

		$this->get_field(
			esc_html__( 'Venue URL', 'seo-by-rank-math' ),
			'location.url'
		);

		$this->get_field(
			esc_html__( 'Address', 'seo-by-rank-math' ),
			'location.address'
		);
	}
	?>

	<?php
	if ( $is_online ) {
		$this->get_field(
			esc_html__( 'Online Event URL', 'seo-by-rank-math' ),
			'VirtualLocation.url'
		);
	}
	?>

	<?php
	$this->get_field(
		esc_html__( 'Performer', 'seo-by-rank-math' ),
		'performer.@type'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Performer Name', 'seo-by-rank-math' ),
		'performer.name'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Performer URL', 'seo-by-rank-math' ),
		'performer.sameAs'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Start Date', 'seo-by-rank-math' ),
		'startDate',
		true
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'End Date', 'seo-by-rank-math' ),
		'endDate',
		true
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Ticket URL', 'seo-by-rank-math' ),
		'offers.url'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Entry Price', 'seo-by-rank-math' ),
		'offers.price'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Currency', 'seo-by-rank-math' ),
		'offers.priceCurrency'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Availability', 'seo-by-rank-math' ),
		'offers.availability'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Availability Starts', 'seo-by-rank-math' ),
		'startDate'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Stock Inventory', 'seo-by-rank-math' ),
		'offers.inventoryLevel'
	);
	?>

	<?php $this->show_ratings(); ?>

</div>
