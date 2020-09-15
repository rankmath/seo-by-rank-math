<?php
/**
 * The Event Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Event class.
 */
class Event implements Snippet {

	/**
	 * Event rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$type             = Helper::get_post_meta( 'snippet_event_type' );
		$this->event_mode = Helper::get_post_meta( 'snippet_event_attendance_mode' );

		$entity = [
			'@type'               => $type ? $type : 'Event',
			'name'                => $jsonld->parts['title'],
			'description'         => $jsonld->parts['desc'],
			'eventStatus'         => $this->get_event_status(),
			'eventAttendanceMode' => $this->get_attendance_mode(),
			'location'            => $this->get_event_location( $jsonld ),
			'offers'              => [
				'@type'    => 'Offer',
				'name'     => 'General Admission',
				'category' => 'primary',
			],
		];

		if ( $start_date = Helper::get_post_meta( 'snippet_event_startdate' ) ) { // phpcs:ignore
			$entity['startDate'] = str_replace( ' ', 'T', Helper::convert_date( $start_date, 'offline' !== $this->event_mode ) );
		}
		if ( $end_date = Helper::get_post_meta( 'snippet_event_enddate' ) ) { // phpcs:ignore
			$entity['endDate'] = str_replace( ' ', 'T', Helper::convert_date( $end_date ) );
		}

		$jsonld->add_ratings( 'event', $entity );

		$jsonld->set_data(
			[
				'snippet_event_price'               => 'price',
				'snippet_event_currency'            => 'priceCurrency',
				'snippet_event_ticketurl'           => 'url',
				'snippet_event_inventory'           => 'inventoryLevel',
				'snippet_event_availability'        => 'availability',
				'snippet_event_availability_starts' => 'validFrom',
			],
			$entity['offers']
		);

		if ( ! empty( $entity['offers']['validFrom'] ) ) {
			$entity['offers']['validFrom'] = str_replace( ' ', 'T', Helper::convert_date( $entity['offers']['validFrom'] ) );
		}

		if ( empty( $entity['offers']['price'] ) ) {
			$entity['offers']['price'] = 0;
		}

		$entity = $this->add_performer( $entity );
		return $entity;
	}

	/**
	 * Add Performer data.
	 *
	 * @param array $entity   Array of JSON-LD data.
	 *
	 * @return array
	 */
	public function add_performer( $entity ) {
		if ( $performer = Helper::get_post_meta( 'snippet_event_performer' ) ) { // phpcs:ignore
			$entity['performer'] = [
				'@type' => Helper::get_post_meta( 'snippet_event_performer_type' ) ? Helper::get_post_meta( 'snippet_event_performer_type' ) : 'Person',
				'name'  => $performer,
			];
			if ( $performer_url = Helper::get_post_meta( 'snippet_event_performer_url' ) ) { // phpcs:ignore
				$entity['performer']['sameAs'] = $performer_url;
			}
		}
		return $entity;
	}

	/**
	 * Get Event Attendance Mode.
	 *
	 * @return string
	 */
	private function get_attendance_mode() {
		if ( ! $this->event_mode || 'offline' === $this->event_mode ) {
			return 'https://schema.org/OfflineEventAttendanceMode';
		}

		if ( 'both' === $this->event_mode ) {
			return 'https://schema.org/MixedEventAttendanceMode';
		}

		if ( 'online' === $this->event_mode ) {
			return 'https://schema.org/OnlineEventAttendanceMode';
		}
	}

	/**
	 * Get Event Status.
	 *
	 * @return array Array of statuses
	 */
	private function get_event_status() {
		$event_status = Helper::get_post_meta( 'snippet_event_status' );
		$status       = [];
		$status[]     = $event_status ? $event_status : 'EventScheduled';

		return $status;
	}

	/**
	 * Get Event Location.
	 *
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array Array of locations.
	 */
	private function get_event_location( $jsonld ) {
		$location = [];

		if ( ! $this->event_mode || in_array( $this->event_mode, [ 'both', 'offline' ], true ) ) {
			$place = [
				'@type' => 'Place',
				'name'  => Helper::get_post_meta( 'snippet_event_venue' ),
				'url'   => Helper::get_post_meta( 'snippet_event_venue_url' ),
			];

			$jsonld->set_address( 'event', $place );
			$location[] = $place;
		}

		if ( in_array( $this->event_mode, [ 'both', 'online' ], true ) ) {
			$location[] = [
				'@type' => 'VirtualLocation',
				'url'   => Helper::get_post_meta( 'snippet_online_event_url' ),
			];
		}

		return $location;
	}
}
