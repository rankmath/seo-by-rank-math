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
	$value      = esc_html__( 'Online + Offline', 'rank-math' );
}
?>
<div class="rank-math-review-data">

	<?php $this->get_description(); ?>

	<?php
	$this->get_field(
		esc_html__( 'Event Type', 'rank-math' ),
		'@type'
	);
	?>

	<?php
	$this->output_field(
		esc_html__( 'Event Attendance Mode', 'rank-math' ),
		$value
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Event Status', 'rank-math' ),
		'eventStatus'
	);
	?>

	<?php
	if ( $is_offline ) {
		$this->get_field(
			esc_html__( 'Venue Name', 'rank-math' ),
			'location.name'
		);

		$this->get_field(
			esc_html__( 'Venue URL', 'rank-math' ),
			'location.url'
		);

		$this->get_field(
			esc_html__( 'Address', 'rank-math' ),
			'location.address'
		);
	}
	?>

	<?php
	if ( $is_online ) {
		$this->get_field(
			esc_html__( 'Online Event URL', 'rank-math' ),
			'VirtualLocation.url'
		);
	}
	?>

	<?php
	$this->get_field(
		esc_html__( 'Performer', 'rank-math' ),
		'performer.@type'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Performer Name', 'rank-math' ),
		'performer.name'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Performer URL', 'rank-math' ),
		'performer.sameAs'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Start Date', 'rank-math' ),
		'startDate',
		true
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'End Date', 'rank-math' ),
		'endDate',
		true
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Ticket URL', 'rank-math' ),
		'offers.url'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Entry Price', 'rank-math' ),
		'offers.price'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Currency', 'rank-math' ),
		'offers.priceCurrency'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Availability', 'rank-math' ),
		'offers.availability'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Availability Starts', 'rank-math' ),
		'startDate'
	);
	?>

	<?php
	$this->get_field(
		esc_html__( 'Stock Inventory', 'rank-math' ),
		'offers.inventoryLevel'
	);
	?>

	<?php $this->show_ratings(); ?>

</div>
