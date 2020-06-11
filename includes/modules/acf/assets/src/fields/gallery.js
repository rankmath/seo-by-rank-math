/**
 * External dependencies
 */
import jQuery from 'jquery'
import { map } from 'lodash'

/**
 * Internal dependencies
 */
import { attachmentCache } from '../attachmentCache'

/**
 * Parse gallery fields.
 *
 * @param {Array} fields Array of fields.
 *
 * @return {Array} Array of fields with content.
 */
export default ( fields ) => {
	const attachments = []

	fields = map( fields, ( field ) => {
		if ( 'gallery' !== field.type ) {
			return field
		}

		field.content = ''

		field.$el.find( '.acf-gallery-attachment input[type=hidden]' ).each( function() {
			const attachmentID = jQuery( this ).val()
			attachments.push( attachmentID )

			// If we have the attachment data in the cache we can return a useful value
			field.content += attachmentCache.getAttachmentContent( attachmentID )
		} )

		return field
	} )

	attachmentCache.refresh( attachments )

	return fields
}
