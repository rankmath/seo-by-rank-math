/**
 * External dependencies
 */
import { v4 as uuid } from 'uuid'

/**
 * Get group default value.
 *
 * @return {Object} Group default value.
 */
export function getGroupDefault() {
	return {
		id: `g-${ uuid() }`,
		property: '',
		properties: [],
		map: {
			isGroup: true,
			isArray: false,
			isRequired: false,
			isRecommended: false,
		},
	}
}

/**
 * Get property default value.
 *
 * @return {Object} Property default value.
 */
export function getPropertyDefault() {
	return {
		id: `p-${ uuid() }`,
		property: '',
		value: '',
		map: {
			isGroup: false,
			isArray: false,
			isRequired: false,
			isRecommended: false,
		},
	}
}

/**
 * Change ids.
 *
 * @param  {Object} data Data.
 * @return {Object} New object.
 */
export function changeIds( data ) {
	data.id = `g-${ uuid() }`
	data.properties.forEach( ( property ) => {
		if ( property.map.isGroup ) {
			changeIds( property )
			return
		}

		property.id = `p-${ uuid() }`
	} )

	return data
}
