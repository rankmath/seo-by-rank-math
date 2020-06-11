/**
 * External dependencies
 */
import { find, map } from 'lodash'

const isHeadline = function( field ) {
	let level = find( rankMath.acf.headlines, ( value, key ) => field.key === key )

	// It has to be an integer
	if ( level ) {
		level = parseInt( level, 10 )
	}

	// Headlines only exist from h1 to h6
	if ( level < 1 || level > 6 ) {
		level = false
	}

	return level
}

const wrapInHeadline = function( field ) {
	const level = isHeadline( field )

	field.content = level ? '<h' + level + '>' + field.content + '</h' + level + '>' :
		'<p>' + field.content + '</p>'

	return field
}

/**
 * Parse text fields.
 *
 * @param {Array} fields Array of fields.
 *
 * @return {Array} Array of fields with content.
 */
export default ( fields ) => {
	fields = map( fields, ( field ) => {
		if ( 'text' !== field.type ) {
			return field
		}

		field.content = field.$el.find( 'input[type=text][id^=acf]' ).val()
		field = wrapInHeadline( field )

		return field
	} )

	return fields
}
