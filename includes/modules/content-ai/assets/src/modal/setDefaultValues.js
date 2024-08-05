/**
 * External dependencies
 */
import { map, isUndefined } from 'lodash'

/**
 * Internal dependencies
 */
import getParams from '../helpers/getParams'

const getDefaultValue = ( key, param ) => {
	return ! isUndefined( param.default ) ? param.default : getParams( key ).default
}

export default ( params, choices ) => {
	const defaults = { choices: choices.default }
	const storedAttributes = wp.data.select( 'rank-math-content-ai' ).getContentAiAttributes()

	map(
		params,
		( param, key ) => {
			const value = ! isUndefined( storedAttributes[ key ] ) ? storedAttributes[ key ] : getDefaultValue( key, param )
			if ( ! isUndefined( value ) ) {
				defaults[ key ] = value
			}
		}
	)

	return defaults
}
