/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * Parse textarea fields.
 *
 * @param {Array} fields Array of fields.
 *
 * @return {Array} Array of fields with content.
 */
export default ( fields ) => {
	fields = map( fields, ( field ) => {
		if ( 'textarea' !== field.type ) {
			return field
		}

		field.content = '<p>' + field.$el.find( 'textarea[id^=acf]' ).val() + '</p>'
		return field
	} )

	return fields
}
