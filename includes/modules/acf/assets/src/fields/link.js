/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * Parse link fields.
 *
 * @param {Array} fields Array of fields.
 *
 * @return {Array} Array of fields with content.
 */
export default ( fields ) => {
	return map( fields, ( field ) => {
		if ( 'link' !== field.type ) {
			return field
		}

		const title = field.$el.find( 'input[type=hidden].input-title' ).val()
		const url = field.$el.find( 'input[type=hidden].input-url' ).val()
		const target = field.$el.find( 'input[type=hidden].input-target' ).val()

		field.content = '<a href="' + url + '" target="' + target + '">' + title + '</a>'

		return field
	} )
}
