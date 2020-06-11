/**
 * External dependencies
 */
import moment from 'moment'
import { isEmpty } from 'lodash'

const TIMEZONELESS_FORMAT = 'YYYY-MM-DDTHH:mm:ss'

/**
 * Convert timestamp
 *
 * @param {string} value Value to convert
 *
 * @return {string} Date.
 */
export function convertTimestamp( value ) {
	if ( isEmpty( value ) ) {
		return ''
	}

	return value.includes( 'T' )
		? value
		: moment
			.unix( value )
			.utc( false )
			.format( TIMEZONELESS_FORMAT )
}
