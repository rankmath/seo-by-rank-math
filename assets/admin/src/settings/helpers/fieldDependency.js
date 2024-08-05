/**
 * External dependencies
 */
import { forEach } from 'lodash'

export default ( field ) => {
	const settings = wp.data.select( 'rank-math-settings' ).getSettings().general
	let canAdd = false
	forEach( field.dependency, ( value, key ) => {
		canAdd = settings[ key ] === value
		if ( ! canAdd ) {
			return false
		}
	} )

	return canAdd
}
