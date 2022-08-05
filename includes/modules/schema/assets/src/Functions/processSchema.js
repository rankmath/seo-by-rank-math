/**
 * External dependencies
 */
import { isEmpty, isBoolean, isUndefined, map, get } from 'lodash'

/**
 * WordPress dependencies
 */
import { applyFilters } from '@wordpress/hooks'

/**
 * Process property value.
 *
 * This function includes filters to modify property value.
 *
 * @param  {Object} property Property of which value to process.
 * @return {string} Processed value.
 */
const processValues = ( property ) => {
	let value = false

	value = applyFilters(
		'rank_math_schema_process_value',
		value,
		property
	)

	value = applyFilters(
		'rank_math_schema_process_' + property.property,
		value,
		property
	)

	return value
}

/**
 * Get property value or default value.
 *
 * @param  {Object} property Property of which value to process.
 * @return {string} Processed value.
 */
const getValue = ( property ) => {
	if ( ! isEmpty( property.value ) || ( ! isUndefined( property.map.field ) && 'toggle' === property.map.field.type ) ) {
		return property.value
	}

	const placeholder = get( property, 'map.field.placeholder' )
	if ( ! isEmpty( placeholder ) ) {
		return placeholder
	}

	return get( property, 'map.field.default', false )
}

/**
 * Process schema from maps to json.
 *
 * @param  {Object} data schema as maps.
 * @return {Object} Converted schema.
 */
const processData = ( data ) => {
	if ( isEmpty( data ) || isUndefined( data.properties ) ) {
		return data
	}

	const schema = {}

	if ( 'metadata' in data ) {
		schema.metadata = { ...data.metadata }
		schema.metadata.title = data.property
	}

	map( data.properties, ( property ) => {
		const canSave = get( property, 'map.save', true )
		const isHidden = get( property, 'map.isHidden', false )

		if ( ! isEmpty( property.properties ) && ! isEmpty( property.properties[ 0 ] ) && ! isEmpty( property.properties[ 0 ].properties ) ) {
			property.map.isArray = true
		}

		// Don't save.
		if ( false === canSave || isHidden ) {
			return
		}

		// Save to metadata.
		if ( 'metadata' === canSave ) {
			const value = getValue( property )
			if ( isEmpty( value ) && 'toggle' !== property.map.field.type ) {
				return
			}

			schema.metadata[ property.property ] = value
			return
		}

		const processedValue = processValues( property )

		// Short-circuit.
		if ( false !== processedValue ) {
			schema[ property.property ] = processedValue
			return
		}

		if ( property.map.isArray ) {
			const array = []
			map( property.properties, ( arrayItem ) => {
				array.push( isUndefined( arrayItem.properties ) ? arrayItem.value : processSchema( arrayItem ) )
			} )

			schema[ property.property ] = array
			return
		}

		if ( property.map.isGroup ) {
			const subProperty = processSchema( property )
			const type = get( subProperty, '@type', ! isUndefined( subProperty[ '@id' ] ) ? '' : property.property )
			if ( type ) {
				subProperty[ '@type' ] = type
			}

			schema[ property.property ] = subProperty
			return
		}

		const value = getValue( property )
		if ( ( ! isBoolean( value ) && isEmpty( value ) ) || ! value ) {
			return
		}

		schema[ property.property ] = value
	} )

	return schema
}

/**
 * Process schema from maps to json.
 *
 * @param  {Object} data schema as maps.
 * @return {Object} Converted schema.
 */
export function processSchema( data ) {
	let schema = processData( data )

	schema = applyFilters( 'rank_math_processed_schema_' + schema[ '@type' ], schema )

	return applyFilters( 'rank_math_processed_schema', schema )
}
