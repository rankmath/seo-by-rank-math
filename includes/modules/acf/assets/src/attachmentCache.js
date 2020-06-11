/**
 * External dependencies
 */
import { each, uniq } from 'lodash'

class AttachmentCache {
	/**
	 * Cache Holder
	 *
	 * @type {Object}
	 */
	cache = {}

	refresh( attachmentIDs ) {
		const uncached = this.getUncached( attachmentIDs )

		if ( 0 === uncached.length ) {
			return
		}

		window.wp.ajax.post( 'query-attachments', { query: { post__in: uncached } } )
			.done( ( attachments ) => {
				each( attachments, ( attachment ) => {
					this.setCache( attachment.id, attachment )
					window.RankMathACFAnalysis.refresh()
				} )
			} )
	}

	get( id ) {
		const attachment = this.getCache( id )
		if ( ! attachment ) {
			return false
		}

		const changedAttachment = window.wp.media.attachment( id )
		if ( changedAttachment.has( 'alt' ) ) {
			attachment.alt = changedAttachment.get( 'alt' )
		}

		if ( changedAttachment.has( 'title' ) ) {
			attachment.title = changedAttachment.get( 'title' )
		}

		return attachment
	}

	getAttachmentContent( attachmentID ) {
		let content = ''
		if ( attachmentCache.get( attachmentID, 'attachment' ) ) {
			const attachment = attachmentCache.get( attachmentID, 'attachment' )
			content += '<img src="' + attachment.url + '" alt="' + attachment.alt + '" title="' + attachment.title + '">'
		}

		return content
	}

	setCache( id, value ) {
		this.cache[ id ] = value
	}

	getCache( id ) {
		return id in this.cache ? this.cache[ id ] : false
	}

	getUncached( ids ) {
		ids = uniq( ids )
		return ids.filter( ( id ) => false === this.get( id ) )
	}
}

export const attachmentCache = new AttachmentCache
