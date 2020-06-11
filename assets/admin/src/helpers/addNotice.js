/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * Add notice helper
 *
 * @param {string} msg      Message to display.
 * @param {Object} which    Type of message.
 * @param {string} after    After what DOM node.
 * @param {boolean} fadeout Should fadeout or not.
 */
export default function( msg, which, after, fadeout ) {
	which = which || 'error'
	fadeout = fadeout || false

	const notice = jQuery(
		'<div class="notice notice-' +
			which +
			' is-dismissible"><p>' +
			msg +
			'</p></div>'
	).hide()
	after.next( '.notice' ).remove()
	after.after( notice )
	notice.slideDown()
	jQuery( document ).trigger( 'wp-updates-notice-added' )
	if ( fadeout ) {
		setTimeout( function() {
			notice.fadeOut( function() {
				notice.remove()
			} )
		}, fadeout )
	}
}
