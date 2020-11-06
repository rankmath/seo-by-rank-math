/**
 * External dependencies
 */
import { isEmpty, has } from 'lodash'

/**
 * Find property by id.
 *
 * @param  {string} id     Property id to find.
 * @param  {Object} parent Parent of the property.
 * @return {Object} Property found.
 */
export function findProperty( id, parent ) {
	if ( isEmpty( id ) || parent.id === id ) {
		return parent
	}

	for ( const property of parent.properties ) {
		if ( property.id === id ) {
			return property
		}

		if ( property.map.isGroup ) {
			const subProperty = findProperty( id, property )
			if ( subProperty ) {
				return subProperty
			}
		}
	}
}

/**
 * Find property by name.
 *
 * @param  {string} name   Property name to find.
 * @param  {Object} parent Parent of the property.
 * @return {Object} Property found.
 */
export function findPropertyByName( name, parent ) {
	if ( isEmpty( name ) ) {
		return parent
	}

	for ( const property of parent.properties ) {
		if ( property.property === name ) {
			property.value = has( parent.metadata, name ) ? parent.metadata[ name ] : property.value
			return property
		}
	}

	return false
}
