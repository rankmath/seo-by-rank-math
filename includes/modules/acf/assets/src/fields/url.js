/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * Parse url fields.
 *
 * @param {Array} fields Array of fields.
 *
 * @return {Array} Array of fields with content.
 */
export default ( fields ) => {
	fields = map( fields, ( field ) => {
		if ( 'url' !== field.type ) {
			return field
		}

		const content = field.$el.find( 'input[type=url][id^=acf]' ).val()

		field.content = content ? '<a href="' + content + '">' + content + '</a>' : ''

		return field
	} )

	return fields
}
