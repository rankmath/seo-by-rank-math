/**
 * External dependencies
 */
import { get, has, forEach, isArray } from 'lodash'

/**
 * Internal dependencies
 */
import { findPropertyByName } from './findProperty'

const checkDependency = ( currentValue, desiredValue, comparison ) => {
	// Multiple values
	if ( isArray( desiredValue ) &&	'=' === comparison ) {
		return desiredValue.includes( currentValue )
	}
	if ( isArray( desiredValue ) &&	'!=' === comparison ) {
		return ! desiredValue.includes( currentValue )
	}

	if ( '=' === comparison && currentValue === desiredValue ) {
		return true
	}

	if ( '==' === comparison && currentValue === desiredValue ) {
		return true
	}

	if ( '>=' === comparison && currentValue >= desiredValue ) {
		return true
	}

	if ( '<=' === comparison && currentValue <= desiredValue ) {
		return true
	}

	if ( '>' === comparison && currentValue > desiredValue ) {
		return true
	}

	if ( '<' === comparison && currentValue < desiredValue ) {
		return true
	}

	if ( '!=' === comparison && currentValue !== desiredValue ) {
		return true
	}

	return false
}

/**
 * Check for dependencies.
 *
 * @param  {Object} property Property object.
 * @param  {Array}  schema
 * @return {boolean} Return True if visible, False if hidden.
 */
export function validateDependency( property, schema ) {
	if ( ! has( property, 'map.dependency' ) ) {
		return true
	}

	let canAdd = null
	const dependencies = property.map.dependency
	const relation = get( dependencies, 'relation', 'or' )

	forEach( dependencies, ( dependency ) => {
		const field = findPropertyByName( dependency.field, schema )
		const result = checkDependency(
			field.value,
			get( dependency, 'value', false ),
			get( dependency, 'comparison', '=' )
		)

		if ( 'or' === relation && result ) {
			canAdd = true
			return false
		} else if ( 'and' === relation ) {
			if ( null === canAdd ) {
				canAdd = result
			} else {
				canAdd = canAdd && result
			}
		}
	} )

	return canAdd ? true : false
}
