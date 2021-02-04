/**
 * External dependencies
 */
import { get, has, forEach, isArray, isObject, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import {
	findPropertyByName,
	getMap,
	generateSchemaFromMap,
	getGroupDefault,
	getPropertyDefault,
} from '@schema/functions'

/**
 * Get schema object for builder based on data.
 *
 * @param  {Object} schema Schema builder object.
 * @param  {Object} json   Schema data.
 * @return {Object} Schema for builder.
 */
export function getSchemaFromData( schema, json ) {
	if ( isEmpty( json ) ) {
		return schema
	}

	forEach( json, ( value, key ) => {
		if ( '@context' === key ) {
			return
		}

		let property = findPropertyByName( key, schema )
		if ( property ) {
			convertValues( property, key, value )
			return
		}

		const map = getMap( key )
		if ( isArray( value ) ) {
			property = getGroupDefault()
			property.map.isArray = true
		} else if ( isObject( value ) || map ) {
			property = getSchemaFromData(
				map ? generateSchemaFromMap( map ) : getGroupDefault(),
				value
			)
		} else {
			property = getPropertyDefault()
		}

		convertValues( property, key, value )
		property.property = key

		schema.properties.push( property )
	} )

	return schema
}

/**
 * Convert values for builer if required.
 *
 * @param  {Object}       property Property to process.
 * @param  {string}       key      Property key.
 * @param  {Array|string} value    Property value.
 * @return {Object} Processed property.
 */
const convertValues = ( property, key, value ) => {
	if ( ! key ) {
		return property
	}

	let newProperty = applyFilters(
		'rank_math_schema_convert_value',
		false,
		property,
		key,
		value
	)
	if ( false !== newProperty ) {
		return newProperty
	}

	newProperty = applyFilters(
		'rank_math_schema_convert_' + key,
		false,
		property,
		value
	)
	if ( false !== newProperty ) {
		return newProperty
	}

	property.value = value

	return property
}

/**
 * Get schema object for builder based on data and map.
 *
 * @param  {Object} data Schema data.
 * @param  {Object} map  Schema map.
 * @return {Object} Schema for builder.
 */
export function generateValidSchemaByMap( data, map ) {
	const schema = generateSchemaFromMap( map )

	return getSchemaFromData( schema, data )
}

/**
 * Get schema object for builder based on data.
 *
 * @param  {Object} json Schema data.
 * @return {Object} Schema for builder.
 */
export function generateValidSchema( json ) {
	const data = { ...json }
	const type = applyFilters( 'rank_math_schema_type', get( data, '@type', '' ) )

	// Get metadata
	const metadata = get( data, 'metadata', { type: 'template' } )
	delete data.metadata

	// Generate schema from map.
	let schema = 'custom' === metadata.type ? getGroupDefault() : generateSchemaFromMap( type )
	if ( has( schema.map, 'title' ) && ! has( metadata, 'title' ) ) {
		metadata.title = schema.map.title
	}

	if ( has( metadata, 'title' ) && metadata.title === schema.map.defaultEn ) {
		metadata.title = schema.map.title
	}

	schema.property = get( metadata, 'title', type )
	schema.metadata = metadata

	// Remove Type.
	const newTypeProperty = findPropertyByName( '@type', schema )
	if ( false !== newTypeProperty && '' !== newTypeProperty.value ) {
		data[ '@type' ] = newTypeProperty.value
	}

	// Move type to first.
	schema = getSchemaFromData( schema, data )
	if ( 'custom' !== metadata.type ) {
		const typeProperty = schema.properties.pop()
		schema.properties.unshift( typeProperty )
	}

	return schema
}
