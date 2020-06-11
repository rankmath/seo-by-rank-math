/*global acf*/

/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * Parse taxonomy fields.
 *
 * @param {Array} fields Array of fields.
 *
 * @return {Array} Array of fields with content.
 */
export default ( fields ) => {
	fields = map( fields, ( field ) => {
		if ( 'taxonomy' !== field.type ) {
			return field
		}

		let terms = []

		if ( field.$el.find( '.acf-taxonomy-field[data-type="multi_select"]' ).length > 0 ) {
			const select2Target = ( acf.select2.version >= 4 ) ? 'select' : 'input'

			terms = map( field.$el.find( '.acf-taxonomy-field[data-type="multi_select"] ' + select2Target ).select2( 'data' ), 'text' )
		} else if ( field.$el.find( '.acf-taxonomy-field[data-type="checkbox"]' ).length > 0 ) {
			terms = map( field.$el.find( '.acf-taxonomy-field[data-type="checkbox"] input[type="checkbox"]:checked' ).next(), 'textContent' )
		} else if ( field.$el.find( 'input[type=checkbox]:checked' ).length > 0 ) {
			terms = map( field.$el.find( 'input[type=checkbox]:checked' ).parent(), 'textContent' )
		} else if ( field.$el.find( 'select option:checked' ).length > 0 ) {
			terms = map( field.$el.find( 'select option:checked' ), 'textContent' )
		}

		terms = map( terms, ( term ) => term.trim() )

		if ( terms.length > 0 ) {
			field.content = '<ul>\n<li>' + terms.join( '</li>\n<li>' ) + '</li>\n</ul>'
		}

		return field
	} )

	return fields
}
