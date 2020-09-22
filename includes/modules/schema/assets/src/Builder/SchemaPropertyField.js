/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import { has, isArray } from 'lodash'

/**
 * WordPress dependencies
 */
import {
	RadioControl,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components'

/**
 * Internal dependencies
 */
import DatePicker from '@components/DatePicker'
import DateTimePicker from '@components/DateTimePicker'
import { sanitizeChoices } from '@helpers/sanitize'

/**
 * Schema property field type component.
 *
 * @param {Object} props This component's props.
 */
const SchemaPropertyField = ( {
	value,
	onChange,
	type,
	options = {},
	...rest
} ) => {
	if ( 'radio' === type ) {
		return (
			<RadioControl
				selected={ value }
				options={ sanitizeChoices( options ) }
				onChange={ onChange }
				{ ...rest }
			/>
		)
	}

	if ( 'select' === type ) {
		if ( ! has( rest, 'multiple' ) && isArray( value ) ) {
			value = value[ 0 ]
		}

		return (
			<SelectControl
				value={ value }
				options={ sanitizeChoices( options ) }
				onChange={ onChange }
				{ ...rest }
			/>
		)
	}

	if ( 'toggle' === type ) {
		return (
			<ToggleControl
				checked={ value }
				onChange={ onChange }
				{ ...rest }
			/>
		)
	}

	if ( 'number' === type ) {
		return (
			<TextControl
				type="number"
				autoComplete="off"
				value={ value }
				onChange={ onChange }
				{ ...rest }
			/>
		)
	}

	if ( 'url' === type ) {
		return (
			<TextControl
				type="url"
				autoComplete="off"
				value={ value }
				onChange={ onChange }
				{ ...rest }
			/>
		)
	}

	if ( 'datepicker' === type ) {
		return (
			<DatePicker
				value={ value }
				position="middle left"
				onChange={ onChange }
			>
				<TextControl value={ value } onChange={ onChange } { ...rest } />
			</DatePicker>
		)
	}

	if ( 'datetimepicker' === type ) {
		return (
			<DateTimePicker
				value={ value }
				position="middle left"
				onChange={ onChange }
			>
				<TextControl value={ value } onChange={ onChange } { ...rest } />
			</DateTimePicker>
		)
	}

	if ( 'textarea' === type ) {
		return (
			<TextareaControl
				rows={ 5 }
				value={ value }
				onChange={ onChange }
				{ ...rest }
			/>
		)
	}

	return (
		<TextareaControl
			rows={ 1 }
			value={ value }
			onChange={ onChange }
			{ ...rest }
		/>
	)
}

SchemaPropertyField.defaultProps = {
	value: '',
	type: 'text',
	onChange: null,
}

SchemaPropertyField.propTypes = {
	id: PropTypes.string,
	onChange: PropTypes.func.isRequired,
}

export default SchemaPropertyField
