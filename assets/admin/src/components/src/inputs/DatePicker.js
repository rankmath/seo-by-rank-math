/**
 * External dependencies
 */
import { includes, map, split } from 'lodash'

/**
 * WordPress dependencies
 */
import { DatePicker } from '@wordpress/components'

/**
 * Internal dependencies
 */
import TextControl from './TextControl'
import useClickOutside from '../hooks/useClickOutside'
import './scss/DatePicker.scss'

/**
 * Date Picker component.
 *
 * @param {Object}   props            Component props.
 * @param {Date}     props.value      The current date value.
 * @param {Function} props.onChange   Callback invoked whan the date value changes.
 * @param {Object}   props.inputProps Properties to be passed to the Text Control component.
 */
export default ( { value, onChange, inputProps, ...rest } ) => {
	const [ showDatePicker, setShowDatePicker, ref ] = useClickOutside()

	/**
	 * Callback executed when when a new date has been selected.
	 *
	 * @param {string} newDate
	 */
	const handleDateChange = ( newDate ) => {
		const date = newDate.split( 'T' )[ 0 ]

		setShowDatePicker( false )

		onChange( date )
	}

	/**
	 * Restrict allowed keys for the input element.
	 *
	 * @param {InputEvent} event
	 */
	const restrictInput = ( event ) => {
		const allowedKeys = [
			'0',
			'1',
			'2',
			'3',
			'4',
			'5',
			'6',
			'7',
			'8',
			'9',
			'-',
			'Backspace',
			'ArrowLeft',
			'ArrowRight',
		]

		if ( ! includes( allowedKeys, event.key ) ) {
			event.preventDefault()
		}
	}

	/**
	 * Check if input value follows the date format.
	 *
	 * @param {InputEvent} event
	 */
	const validateDate = ( event ) => {
		const datePattern = /^\d{4}-\d{2}-\d{2}$/
		const { value: inputValue } = event.target

		// Check date format
		if ( ! datePattern.test( inputValue ) ) {
			onChange( '' )
			return
		}

		const [ year, month, day ] = map( split( inputValue, '-' ), Number )
		const date = new Date( year, month - 1, day )

		// Check date validity
		if (
			date.getFullYear() === year &&
			date.getMonth() === month - 1 &&
			date.getDate() === day
		) {
			inputProps.onBlur( event )
		} else {
			onChange( '' )
		}
	}

	return (
		<div
			ref={ ref }
			className={ `rank-math-date-picker ${
				showDatePicker ? 'show-date-picker' : 'hide-date-picker'
			}` }
		>
			<TextControl
				{ ...inputProps }
				value={ value }
				onChange={ onChange }
				onClick={ () => setShowDatePicker( true ) }
				onKeyDown={ restrictInput }
				onBlur={ validateDate }
			/>

			<DatePicker { ...rest } onChange={ handleDateChange } />
		</div>
	)
}
