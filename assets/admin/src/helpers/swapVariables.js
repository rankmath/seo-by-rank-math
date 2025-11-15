/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * Swap Variables.
 */
class SwapVariables {
	/**
	 * Variable map
	 *
	 * @type {Object}
	 */
	map = null

	swap( str, map ) {
		str = str || ''
		if ( ! str ) {
			return ''
		}

		const regex = new RegExp( /%(([a-z0-9_-]+)\(([^)]*)\)|[^\s]+)%/, 'giu' )
		return str
			.replace( ' %page%', '' )
			.replace( '%sep% %sep%', '%sep%' )
			.replace( regex, ( matched ) => {
				return this.replace( map, matched )
			} )
			.trim()
	}

	replace( map, matched ) {
		let token = matched.toLowerCase().slice( 1, -1 )

		if ( [ 'term_description', 'user_description' ].includes( token ) ) {
			return 'undefined' !== typeof tinymce &&
				'undefined' !== typeof tinymce.editors.rank_math_description_editor
				? tinymce.editors.rank_math_description_editor.getContent()
				: jQuery( '#description' ).val()
		}

		if ( token.includes( 'customfield(' ) ) {
			token = token.replace( 'customfield(', '' ).replace( ')', '' )
			return token in rankMath.customFields
				? rankMath.customFields[ token ]
				: ''
		}

		map = map || this.getMap()
		token = token.includes( '(' ) ? token.split( '(' )[ 0 ] : token
		token = 'seo_title' === token ? 'title' : token
		token = 'seo_description' === token ? 'excerpt' : token

		return token in map ? map[ token ] : ''
	}

	getMap() {
		if ( null !== this.map ) {
			return this.map
		}

		this.map = {}
		jQuery.each( rankMath.variables, ( key, item ) => {
			key = key
				.toLowerCase()
				.replace( /%+/g, '' )
				.split( '(' )[ 0 ]
			this.map[ key ] = item.example
		} )

		return this.map
	}

	setVariable( key, value ) {
		if ( null !== this.map ) {
			this.map[ key ] = value
		} else if ( undefined !== rankMath.variables[ key ] ) {
			rankMath.variables[ key ].example = value
		}
	}
}

export const swapVariables = new SwapVariables()
