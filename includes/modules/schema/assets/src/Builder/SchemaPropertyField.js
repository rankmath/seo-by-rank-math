/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import { has, isArray, includes, remove, uniqueId, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import {
	RadioControl,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
	CheckboxControl,
} from '@wordpress/components'
import { RawHTML } from '@wordpress/element'

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
 * @param {string|Array} props.value Field value.
 * @param {Function} props.onChange Callback function.
 * @param {string} props.type Field type.
 * @param {Object} props.options Field options.
 */
const SchemaPropertyField = ( {
	value,
	onChange,
	type,
	options = {},
	...rest
} ) => {
	if ( ! isUndefined( rest.help ) ) {
		rest.help = RawHTML( { children: rest.help } )
	}

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
				step="0.01"
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

	if ( 'checkbox' === type ) {
		return (
			<div className="rank-math-checkbox-component components-base-control schema-property--value">
				<label htmlFor="checklist-label" className="components-base-control__label">{ rest.label }</label>
				<div>
					{ sanitizeChoices( options ).map( ( data ) => {
						return (
							<CheckboxControl
								key={ uniqueId( 'checkbox-' ) }
								label={ data.label }
								checked={ includes( value, data.value ) }
								onChange={ ( check ) => {
									const newValues = [ ...value ]
									if ( check ) {
										newValues.push( data.value )
									} else {
										remove( newValues, ( n ) => ( n === data.value ) )
									}

									onChange( newValues )
								} }
							/>
						)
					} ) }
				</div>
			</div>
		)
	}

	return (
		<TextControl
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
