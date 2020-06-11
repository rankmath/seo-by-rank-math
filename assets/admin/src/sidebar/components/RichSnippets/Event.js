/**
 * External dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, SelectControl, TextControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Address from '@components/Address'
import { convertTimestamp } from '@helpers/time'
import DateTimePicker from '@components/DateTimePicker'

const EventSnippet = ( props ) => {
	const eventEnddate = convertTimestamp( props.eventEnddate )
	const eventStartdate = convertTimestamp( props.eventStartdate )
	const eventAvailabilityStarts = convertTimestamp(
		props.eventAvailabilityStarts
	)

	return (
		<Fragment>
			<PanelBody initialOpen={ true }>
				<SelectControl
					label={ __( 'Event Type', 'rank-math' ) }
					help={ __( 'Type of the event', 'rank-math' ) }
					value={ props.eventType }
					options={ [
						{
							value: 'Event',
							label: __( 'Event', 'rank-math' ),
						},
						{
							value: 'BusinessEvent',
							label: __( 'Business Event', 'rank-math' ),
						},
						{
							value: 'ChildrensEvent',
							label: __( 'Childrens Event', 'rank-math' ),
						},
						{
							value: 'ComedyEvent',
							label: __( 'Comedy Event', 'rank-math' ),
						},
						{
							value: 'DanceEvent',
							label: __( 'Dance Event', 'rank-math' ),
						},
						{
							value: 'DeliveryEvent',
							label: __( 'Delivery Event', 'rank-math' ),
						},
						{
							value: 'EducationEvent',
							label: __( 'Education Event', 'rank-math' ),
						},
						{
							value: 'ExhibitionEvent',
							label: __( 'Exhibition Event', 'rank-math' ),
						},
						{
							value: 'Festival',
							label: __( 'Festival', 'rank-math' ),
						},
						{
							value: 'FoodEvent',
							label: __( 'Food Event', 'rank-math' ),
						},
						{
							value: 'LiteraryEvent',
							label: __( 'Literary Event', 'rank-math' ),
						},
						{
							value: 'MusicEvent',
							label: __( 'Music Event', 'rank-math' ),
						},
						{
							value: 'PublicationEvent',
							label: __( 'Publication Event', 'rank-math' ),
						},
						{
							value: 'SaleEvent',
							label: __( 'Sale Event', 'rank-math' ),
						},
						{
							value: 'ScreeningEvent',
							label: __( 'Screening Event', 'rank-math' ),
						},
						{
							value: 'SocialEvent',
							label: __( 'Social Event', 'rank-math' ),
						},
						{
							value: 'SportsEvent',
							label: __( 'Sports Event', 'rank-math' ),
						},
						{
							value: 'TheaterEvent',
							label: __( 'Theater Event', 'rank-math' ),
						},
						{
							value: 'VisualArtsEvent',
							label: __( 'Visual Arts Event', 'rank-math' ),
						},
					] }
					onChange={ props.updateType }
				/>

				<SelectControl
					label={ __( 'Event Status', 'rank-math' ) }
					help={ __(
						'Current status of the event (optional)',
						'rank-math'
					) }
					value={ props.eventStatus }
					options={ [
						{
							value: '',
							label: __( 'None', 'rank-math' ),
						},
						{
							value: 'EventScheduled',
							label: __( 'Scheduled', 'rank-math' ),
						},
						{
							value: 'EventCancelled',
							label: __( 'Cancelled', 'rank-math' ),
						},
						{
							value: 'EventPostponed',
							label: __( 'Postponed', 'rank-math' ),
						},
						{
							value: 'EventRescheduled',
							label: __( 'Rescheduled', 'rank-math' ),
						},
						{
							value: 'EventMovedOnline',
							label: __( 'Moved Online', 'rank-math' ),
						},
					] }
					onChange={ props.updateStatus }
				/>

				<SelectControl
					label={ __( 'Event Attendance Mode', 'rank-math' ) }
					help={ __(
						'Indicates whether the event occurs online, offline at a physical location, or a mix of both online and offline.',
						'rank-math'
					) }
					value={ props.eventAttendanceMode }
					options={ [
						{
							value: 'offline',
							label: __( 'Offline', 'rank-math' ),
						},
						{
							value: 'online',
							label: __( 'Online', 'rank-math' ),
						},
						{
							value: 'both',
							label: __( 'Online + Offline', 'rank-math' ),
						},
					] }
					onChange={ props.updateAttendanceMode }
				/>

				{ true ===
					includes(
						[ 'online', 'both' ],
						props.eventAttendanceMode
					) && (
					<TextControl
						type="url"
						label={ __( 'Online Event URL', 'rank-math' ) }
						help={ __(
							'The URL of the online event, where people can join. This property is required if your event is happening online.',
							'rank-math'
						) }
						value={ props.onlineEventUrl }
						onChange={ props.updateEventUrl }
					/>
				) }

				{ true ===
					includes(
						[ '', 'offline', 'both' ],
						props.eventAttendanceMode
					) && (
					<TextControl
						label={ __( 'Venue Name', 'rank-math' ) }
						help={ __( 'The venue name', 'rank-math' ) }
						value={ props.eventVenue }
						onChange={ props.updateVenue }
					/>
				) }

				{ true ===
					includes(
						[ '', 'offline', 'both' ],
						props.eventAttendanceMode
					) && (
					<TextControl
						type="url"
						label={ __( 'Venue URL', 'rank-math' ) }
						help={ __( 'Website URL of the venue', 'rank-math' ) }
						value={ props.eventVenueUrl }
						onChange={ props.updateVenueUrl }
					/>
				) }
			</PanelBody>

			{ true ===
				includes(
					[ '', 'offline', 'both' ],
					props.eventAttendanceMode
				) && (
				<Address
					label={ __( 'Address', 'rank-math' ) }
					initialOpen={ true }
					value={
						'' !== props.eventAddress ? props.eventAddress : {}
					}
					onChange={ props.updateAddress }
				/>
			) }

			<PanelBody initialOpen={ true }>
				<SelectControl
					label={ __( 'Performer', 'rank-math' ) }
					help={ __( 'Type of the event', 'rank-math' ) }
					value={ props.eventPerformerType }
					options={ [
						{
							value: 'Person',
							label: __( 'Person', 'rank-math' ),
						},
						{
							value: 'Organization',
							label: __( 'Organization', 'rank-math' ),
						},
					] }
					onChange={ props.updatePerformerType }
				/>

				<TextControl
					label={ __( 'Performer Name', 'rank-math' ) }
					help={ __( 'A performer at the event', 'rank-math' ) }
					value={ props.eventPerformer }
					onChange={ props.updatePerformer }
				/>

				<TextControl
					type="url"
					label={ __( 'Performer URL', 'rank-math' ) }
					value={ props.eventPerformerUrl }
					onChange={ props.updatePerformerUrl }
				/>

				<DateTimePicker
					isTimestamp={ true }
					value={ eventStartdate }
					onChange={ props.updateStartdate }
				>
					<TextControl
						autoComplete="off"
						label={ __( 'Start Date', 'rank-math' ) }
						help={ __( 'Date and time of the event', 'rank-math' ) }
						value={ eventStartdate }
						onChange={ props.updateStartdate }
					/>
				</DateTimePicker>

				<DateTimePicker
					isTimestamp={ true }
					value={ eventEnddate }
					onChange={ props.updateEnddate }
				>
					<TextControl
						autoComplete="off"
						label={ __( 'End Date', 'rank-math' ) }
						help={ __(
							'End date and time of the event',
							'rank-math'
						) }
						value={ eventEnddate }
						onChange={ props.updateEnddate }
					/>
				</DateTimePicker>

				<TextControl
					type="url"
					label={ __( 'Ticket URL', 'rank-math' ) }
					help={ __(
						'A URL where visitors can purchase tickets for the event',
						'rank-math'
					) }
					value={ props.eventTicketurl }
					onChange={ props.updateTicketurl }
				/>

				<TextControl
					type="number"
					step="any"
					label={ __( 'Entry Price', 'rank-math' ) }
					help={ __(
						'Entry price of the event (optional)',
						'rank-math'
					) }
					value={ props.eventPrice }
					onChange={ props.updatePrice }
				/>

				<TextControl
					label={ __( 'Currency', 'rank-math' ) }
					help={ __(
						'ISO 4217 Currency code. Example: EUR',
						'rank-math'
					) }
					value={ props.eventCurrency }
					onChange={ props.updateCurrency }
				/>

				<SelectControl
					label={ __( 'Availability', 'rank-math' ) }
					help={ __( 'Offer availability', 'rank-math' ) }
					value={ props.eventAvailability }
					options={ [
						{
							value: '',
							label: __( 'None', 'rank-math' ),
						},
						{
							value: 'InStock',
							label: __( 'In Stock', 'rank-math' ),
						},
						{
							value: 'SoldOut',
							label: __( 'Sold Out', 'rank-math' ),
						},
						{
							value: 'PreOrder',
							label: __( 'Preorder', 'rank-math' ),
						},
					] }
					onChange={ props.updateAvailability }
				/>

				<DateTimePicker
					isTimestamp={ true }
					value={ eventAvailabilityStarts }
					onChange={ props.updateAvailabilityStarts }
				>
					<TextControl
						autoComplete="off"
						label={ __( 'Availability Starts', 'rank-math' ) }
						help={ __(
							'Date and time when offer is made available. (optional)',
							'rank-math'
						) }
						value={ eventAvailabilityStarts }
						onChange={ props.updateAvailabilityStarts }
					/>
				</DateTimePicker>

				<TextControl
					label={ __( 'Stock Inventory', 'rank-math' ) }
					help={ __( 'Number of tickets (optional)', 'rank-math' ) }
					value={ props.eventInventory }
					onChange={ props.updateInventory }
				/>

				<TextControl
					type="number"
					label={ __( 'Rating', 'rank-math' ) }
					help={ __(
						'Rating score of the event. Optional.',
						'rank-math'
					) }
					autoComplete="off"
					step="any"
					value={ props.eventRating }
					onChange={ props.updateRating }
				/>

				<TextControl
					type="number"
					label={ __( 'Rating Minimum', 'rank-math' ) }
					help={ __(
						'Rating minimum score of the event.',
						'rank-math'
					) }
					autoComplete="off"
					value={ props.eventRatingMin }
					onChange={ props.updateRatingMin }
				/>

				<TextControl
					type="number"
					label={ __( 'Rating Maximum', 'rank-math' ) }
					help={ __(
						'Rating maximum score of the event.',
						'rank-math'
					) }
					autoComplete="off"
					value={ props.eventRatingMax }
					onChange={ props.updateRatingMax }
				/>
			</PanelBody>
		</Fragment>
	)
}

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			eventType: data.eventType,
			eventVenue: data.eventVenue,
			eventVenueUrl: data.eventVenueUrl,
			eventAddress: data.eventAddress,
			eventPerformerType: data.eventPerformerType,
			eventPerformer: data.eventPerformer,
			eventPerformerUrl: data.eventPerformerUrl,
			eventStatus: data.eventStatus,
			eventStartdate: data.eventStartdate,
			eventEnddate: data.eventEnddate,
			eventTicketurl: data.eventTicketurl,
			eventPrice: data.eventPrice,
			eventCurrency: data.eventCurrency,
			eventAvailability: data.eventAvailability,
			eventAvailabilityStarts: data.eventAvailabilityStarts,
			eventInventory: data.eventInventory,
			eventRating: data.eventRating,
			eventRatingMin: data.eventRatingMin,
			eventRatingMax: data.eventRatingMax,
			eventAttendanceMode: data.eventAttendanceMode,
			onlineEventUrl: data.onlineEventUrl,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateType( type ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventType',
					'event_type',
					type
				)
			},

			updateVenue( venue ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventVenue',
					'event_venue',
					venue
				)
			},

			updateVenueUrl( url ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventVenueUrl',
					'event_venue_url',
					url
				)
			},

			updateEventUrl( url ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'onlineEventUrl',
					'online_event_url',
					url
				)
			},

			updateAttendanceMode( mode ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventAttendanceMode',
					'event_attendance_mode',
					mode
				)
			},

			updateAddress( address ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventAddress',
					'event_address',
					address
				)
			},

			updatePerformerType( type ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventPerformerType',
					'event_performer_type',
					type
				)
			},

			updatePerformer( performer ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventPerformer',
					'event_performer',
					performer
				)
			},

			updatePerformerUrl( url ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventPerformerUrl',
					'event_performer_url',
					url
				)
			},

			updateStatus( status ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventStatus',
					'event_status',
					status
				)
			},

			updateStartdate( date ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventStartdate',
					'event_startdate',
					date
				)
			},

			updateEnddate( date ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventEnddate',
					'event_enddate',
					date
				)
			},

			updateTicketurl( url ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventTicketurl',
					'event_ticketurl',
					url
				)
			},

			updatePrice( price ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventPrice',
					'event_price',
					price
				)
			},

			updateCurrency( currency ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventCurrency',
					'event_currency',
					currency
				)
			},

			updateAvailability( status ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventAvailability',
					'event_availability',
					status
				)
			},

			updateAvailabilityStarts( date ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventAvailabilityStarts',
					'event_availability_starts',
					date
				)
			},

			updateInventory( inventory ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventInventory',
					'event_inventory',
					inventory
				)
			},

			updateRating( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventRating',
					'event_rating',
					rating
				)
			},

			updateRatingMin( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventRatingMin',
					'event_rating_min',
					rating
				)
			},

			updateRatingMax( rating ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'eventRatingMax',
					'event_rating_max',
					rating
				)
			},
		}
	} )
)( EventSnippet )
