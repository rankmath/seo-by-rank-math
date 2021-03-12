/**
 * External dependencies
 */
import { get, has, merge, forEach, isString } from 'lodash'

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
			if ( has( value, 'title' ) ) {
				schema.map.title = value.title
				schema.map.defaultEn = value.defaultEn
			}
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
 * @param  {string}  type       Type of schema map.
 * @param  {Object}  arrayProps Properties of map to override.
 * @param  {boolean} isCustom   Whether its a custom schema.
 * @return {Object} Schema for builder.
 */
export function generateSchemaFromMap( type, arrayProps = {}, isCustom = false ) {
	let map = false
	if ( ! isCustom ) {
		map = isString( type ) ? getMap( type ) : type
	}

	let schema = getSchemaFromMap( map )
	schema = merge( schema, arrayProps )
	schema.property = type

	return schema
}
