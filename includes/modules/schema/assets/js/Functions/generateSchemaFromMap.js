/**
 * External dependencies
 */
import { get, merge, forEach, isString } from 'lodash'

/**
 * Internal dependencies
 */
import { getMap, getGroupDefault, getPropertyDefault } from '@schema/functions'

/**
 * Get schema object for builder based on map.
 *
 * @param  {Object} parentMap Schema map.
 * @return {Object} Schema for builder.
 */
const getSchemaFromMap = ( parentMap ) => {
	if ( ! parentMap ) {
		return getGroupDefault()
	}

	const schema = parentMap.map.isGroup
		? getGroupDefault()
		: getPropertyDefault()

	forEach( parentMap, ( value, key ) => {
		if ( 'map' === key ) {
			return
		}

		let property = getPropertyDefault()
		if ( value.map.isGroup ) {
			property = getSchemaFromMap( value )
		}

		property.map = value.map
		property.property = key
		property.value = get( value.map, 'value', get( value.map, 'field.default', '' ) )

		schema.properties.push( property )
	} )

	return schema
}

/**
 * Get schema object for builder based on map.
 *
 * @param  {string} type        Type of schema map.
 * @param  {Object} arrayProps Properties of map to override.
 * @return {Object} Schema for builder.
 */
export function generateSchemaFromMap( type, arrayProps = {} ) {
	const map = isString( type ) ? getMap( type ) : type

	let schema = getSchemaFromMap( map )
	schema = merge( schema, arrayProps )
	schema.property = type

	return schema
}
