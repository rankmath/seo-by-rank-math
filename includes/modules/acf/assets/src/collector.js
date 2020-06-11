/*global acf*/

/**
 * External dependencies
 */
import jQuery from 'jquery'
import { includes, each, filter, map, uniq } from 'lodash'

/**
 * Internal dependencies
 */
import text from './fields/text'
import textarea from './fields/textarea'
import email from './fields/email'
import url from './fields/url'
import link from './fields/link'
import wysiwyg from './fields/wysiwyg'
import image from './fields/image'
import gallery from './fields/gallery'
import taxonomy from './fields/taxonomy'

const fields = {
	text,
	textarea,
	email,
	url,
	link,
	wysiwyg,
	image,
	gallery,
	taxonomy,
}

class Collector {
	getFieldContent() {
		let fieldData = this.excludeNames( this.excludeTypes( this.getData() ) )
		const usedTypes = uniq( map( fieldData, 'type' ) )

		jQuery.each( usedTypes, ( key, type ) => {
			if ( type in fields ) {
				fieldData = fields[ type ]( fieldData )
			}
		} )

		return fieldData
	}

	append( data ) {
		const fieldData = this.getFieldContent()
		each( fieldData, ( field ) => {
			if ( 'undefined' !== typeof field.content && '' !== field.content ) {
				data += '\n' + field.content
			}
		} )

		return data
	}

	getData() {
		const outerFieldsName = [
			'flexible_content',
			'repeater',
			'group',
		]

		const innerFields = []
		const outerFields = []

		/*eslint @wordpress/no-unused-vars-before-return: 0 */
		const acfFields = map( acf.get_fields(), ( field ) => {
			const fieldData = jQuery.extend( true, {}, acf.get_data( jQuery( field ) ) )
			fieldData.$el = jQuery( field )
			fieldData.post_meta_key = fieldData.name

			// Collect nested and parent
			if ( -1 === outerFieldsName.indexOf( fieldData.type ) ) {
				innerFields.push( fieldData )
			} else {
				outerFields.push( fieldData )
			}

			return fieldData
		} )

		if ( 0 === outerFields.length ) {
			return acfFields
		}

		each( innerFields, ( inner ) => {
			each( outerFields, ( outer ) => {
				if ( jQuery.contains( outer.$el[ 0 ], inner.$el[ 0 ] ) ) {
					if ( 'flexible_content' === outer.type || 'repeater' === outer.type ) {
						outer.children = outer.children || []
						outer.children.push( inner )
						inner.parent = outer
						inner.post_meta_key = outer.name + '_' + ( outer.children.length - 1 ) + '_' + inner.name
					}

					// Types that hold single children.
					if ( 'group' === outer.type ) {
						outer.children = [ inner ]
						inner.parent = outer
						inner.post_meta_key = outer.name + '_' + inner.name
					}
				}
			} )
		} )

		return acfFields
	}

	excludeTypes( fieldData ) {
		return filter( fieldData, ( field ) => ! includes( rankMath.acf.blacklistTypes, field.type ) )
	}

	excludeNames( fieldData ) {
		return filter( fieldData, ( field ) => ! includes( rankMath.acf.names, field.name ) )
	}
}

export const collect = new Collector
