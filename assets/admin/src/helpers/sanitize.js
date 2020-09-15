/**
 * External dependencies
 */
import { map } from 'lodash'

export function sanitizeChoices( options ) {
	return map( options, ( label, value ) => {
		return { label, value }
	} )
}
