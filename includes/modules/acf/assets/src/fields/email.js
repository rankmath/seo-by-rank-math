/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * Parse email fields.
 *
 * @param {Array} fields Array of fields.
 *
 * @return {Array} Array of fields with content.
 */
export default ( fields ) => {
	fields = map( fields, ( field ) => {
		if ( 'email' !== field.type ) {
			return field
		}

		field.content = field.$el.find( 'input[type=email][id^=acf]' ).val()

		return field
	} )

	return fields
}
