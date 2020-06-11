/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * Internal dependencies
 */
import { attachmentCache } from '../attachmentCache'

/**
 * Parse image fields.
 *
 * @param {Array} fields Array of fields.
 *
 * @return {Array} Array of fields with content.
 */
export default ( fields ) => {
	const attachments = []

	fields = map( fields, ( field ) => {
		if ( 'image' !== field.type ) {
			return field
		}

		field.content = ''

		const attachmentID = field.$el.find( 'input[type=hidden]' ).val()
		attachments.push( attachmentID )

		// If we have the attachment data in the cache we can return a useful value
		field.content += attachmentCache.getAttachmentContent( attachmentID )

		return field
	} )

	attachmentCache.refresh( attachments )

	return fields
}
