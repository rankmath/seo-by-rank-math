/**
 * External dependencies
 */
import moment from 'moment'
import { isInteger } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Component, Fragment } from '@wordpress/element'
import { Button, Dropdown } from '@wordpress/components'

class TimePickerOnly extends Component {
	constructor() {
		super( ...arguments )
		this.state = {
			hours: '',
			minutes: '',
			am: true,
			date: null,
		}
		this.updateHours = this.updateHours.bind( this )
		this.updateMinutes = this.updateMinutes.bind( this )
		this.onChangeHours = this.onChangeHours.bind( this )
		this.onChangeMinutes = this.onChangeMinutes.bind( this )
	}

	componentDidMount() {
		this.syncState( this.props )
	}

	componentDidUpdate( prevProps ) {
		const { currentTime, is12Hour } = this.props
		if (
			currentTime !== prevProps.currentTime ||
			is12Hour !== prevProps.is12Hour
		) {
			this.syncState( this.props )
		}
	}

	/**
	 * Function that sets the date state and calls the onChange with a new date.
	 * The date is truncated at the minutes.
	 *
	 * @param {Object} newDate The date object.
	 */
	changeDate( newDate ) {
		const dateWithStartOfMinutes = newDate.clone().startOf( 'minute' )
		this.setState( { date: dateWithStartOfMinutes } )
		this.props.onChange(
			newDate.format( this.props.is12Hour ? 'hh:mm A' : 'HH:mm' )
		)
	}

	getMaxHours() {
		return this.props.is12Hour ? 12 : 23
	}

	getMinHours() {
		return this.props.is12Hour ? 1 : 0
	}

	syncState( { currentTime, is12Hour } ) {
		const date = currentTime
			? moment( currentTime, is12Hour ? [ 'hh:m a', 'h:m a' ] : 'HH:m' )
			: moment()
		const minutes = date.format( 'mm' )
		const am = date.format( 'A' )
		const hours = date.format( is12Hour ? 'hh' : 'HH' )
		this.setState( { minutes, hours, am, date } )
	}

	updateHours() {
		const { is12Hour } = this.props
		const { am, hours, date } = this.state
		const value = parseInt( hours, 10 )
		if (
			! isInteger( value ) ||
			( is12Hour && ( value < 1 || value > 12 ) ) ||
			( ! is12Hour && ( value < 0 || value > 23 ) )
		) {
			this.syncState( this.props )
			return
		}

		const newDate = is12Hour
			? date.clone().hours( am === 'AM' ? value % 12 : ( ( ( value % 12 ) + 12 ) % 24 ) )
			: date.clone().hours( value )
		this.changeDate( newDate )
	}

	updateMinutes() {
		const { minutes, date } = this.state
		const value = parseInt( minutes, 10 )
		if ( ! isInteger( value ) || value < 0 || value > 59 ) {
			this.syncState( this.props )
			return
		}
		const newDate = date.clone().minutes( value )
		this.changeDate( newDate )
	}

	updateAmPm( value ) {
		return () => {
			const { am, date, hours } = this.state
			if ( am === value ) {
				return
			}
			let newDate
			if ( value === 'PM' ) {
				newDate = date
					.clone()
					.hours( ( ( parseInt( hours, 10 ) % 12 ) + 12 ) % 24 )
			} else {
				newDate = date.clone().hours( parseInt( hours, 10 ) % 12 )
			}
			this.changeDate( newDate )
		}
	}

	onChangeHours( event ) {
		this.setState( { hours: event.target.value } )
	}

	onChangeMinutes( event ) {
		const minutes = event.target.value
		this.setState( {
			minutes: '' === minutes ? '' : ( '0' + minutes ).slice( -2 ),
		} )
	}

	render() {
		const { is12Hour } = this.props
		const { minutes, hours, am } = this.state
		return (
			<div className="components-datetime__time">
				<fieldset>
					<legend className="components-datetime__time-legend invisible">
						{ __( 'Time' ) }
					</legend>
					<div className="components-datetime__time-wrapper">
						<div className="components-datetime__time-field components-datetime__time-field-time">
							<input
								aria-label={ __( 'Hours' ) }
								className="components-datetime__time-field-hours-input"
								type="number"
								step={ 1 }
								min={ this.getMinHours() }
								max={ this.getMaxHours() }
								value={ hours }
								onChange={ this.onChangeHours }
								onBlur={ this.updateHours }
							/>
							<span
								className="components-datetime__time-separator"
								aria-hidden="true"
							>
								:
							</span>
							<input
								aria-label={ __( 'Minutes' ) }
								className="components-datetime__time-field-minutes-input"
								type="number"
								min={ 0 }
								max={ 59 }
								value={ minutes }
								onChange={ this.onChangeMinutes }
								onBlur={ this.updateMinutes }
							/>
						</div>
						{ is12Hour && (
							<div className="components-datetime__time-field components-datetime__time-field-am-pm">
								<Button
									className="components-datetime__time-am-button"
									isPressed={ am === 'AM' }
									onClick={ this.updateAmPm( 'AM' ) }
								>
									{ __( 'AM' ) }
								</Button>
								<Button
									className="components-datetime__time-pm-button"
									isPressed={ am === 'PM' }
									onClick={ this.updateAmPm( 'PM' ) }
								>
									{ __( 'PM' ) }
								</Button>
							</div>
						) }
					</div>
				</fieldset>
			</div>
		)
	}
}

const EnhancedTimePicker = ( {
	position = 'middle right',
	value,
	onChange,
	children,
} ) => {
	return (
		<Dropdown
			position={ position }
			className="rank-math-datepicker"
			contentClassName="rank-math-datepicker__dialog"
			renderToggle={ ( { onToggle, isOpen } ) => (
				<Fragment>
					{ children }

					<Button
						icon="calendar-alt"
						onClick={ onToggle }
						aria-expanded={ isOpen }
					/>
				</Fragment>
			) }
			renderContent={ () => (
				<TimePickerOnly
					is12Hour={ true }
					currentTime={ value }
					onChange={ onChange }
				/>
			) }
		/>
	)
}

export default EnhancedTimePicker
