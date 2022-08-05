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
 * @param  {Object}  schema   Schema builder object.
 * @param  {Object}  json     Schema data.
 * @param  {boolean} isCustom Whether its a custom schema.
 * @return {Object} Schema for builder.
 */
export function getSchemaFromData( schema, json, isCustom = false ) {
	if ( isEmpty( json ) ) {
		return schema
	}

	forEach( json, ( value, key ) => {
		if ( '@context' === key ) {
			return
		}

		let property = findPropertyByName( key, schema )
		if ( property ) {
			convertValues( property, key, value, isCustom )
			return
		}

		const map = ! isCustom ? getMap( key ) : false
		if ( isArray( value ) ) {
			property = getGroupDefault()
			property.map.isArray = true
		} else if ( isObject( value ) || map ) {
			property = getSchemaFromData(
				map ? generateSchemaFromMap( map ) : getGroupDefault(),
				value,
				isCustom
			)
		} else {
			property = getPropertyDefault()
		}

		convertValues( property, key, value, isCustom )
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
 * @param  {boolean}      isCustom Whether its a custom schema.
 * @return {Object} Processed property.
 */
const convertValues = ( property, key, value, isCustom = false ) => {
	if ( ! key ) {
		return property
	}

	let newProperty = applyFilters(
		'rank_math_schema_convert_value',
		false,
		property,
		key,
		value,
		isCustom
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
 * @param  {Object}  data       Schema data.
 * @param  {Object}  map        Schema map.
 * @param  {Object}  arrayProps Schema arrayProps.
 * @param  {boolean} isCustom   If Current schema is custom.
 * @return {Object}             Schema for builder.
 */
export function generateValidSchemaByMap( data, map, arrayProps = {}, isCustom = false ) {
	const schema = generateSchemaFromMap( map, arrayProps, isCustom )

	return getSchemaFromData( schema, data, isCustom )
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
	schema = getSchemaFromData( schema, data, 'custom' === metadata.type )
	if ( 'custom' !== metadata.type ) {
		const typeProperty = schema.properties.pop()
		schema.properties.unshift( typeProperty )
	}

	return schema
}
