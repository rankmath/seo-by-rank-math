/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * Add notice helper
 *
 * @param {string}  msg      Message to display.
 * @param {Object}  which    Type of message.
 * @param {string}  after    After what DOM node.
 * @param {boolean} fadeout  Should fadeout or not.
 * @param {string}  classes  Notice classes.
 * @param {string}  multiple Whether to show multiple notices at the same time.
 */
export default function( msg, which, after, fadeout, classes = '', multiple = false ) {
	which = which || 'error'
	fadeout = fadeout || false

	const notice = jQuery(
		'<div class="notice notice-' +
			which +
			' ' + classes +
			' is-dismissible"><p>' +
			msg +
			'</p></div>'
	).hide()

	// Remove existing notice(s) unless multiple is true
	if ( ! multiple ) {
		after.siblings( '.notice' ).remove()
	}

	// Insert the new notice after the target element
	after.after( notice )
	notice.slideDown()

	// Trigger WP event for added notices
	jQuery( document ).trigger( 'wp-updates-notice-added' )

	// Scroll to the new notice
	jQuery( 'html,body' ).animate(
		{
			scrollTop: notice.offset().top - 50,
		},
		'slow'
	)

	// Optional fadeout
	if ( fadeout ) {
		setTimeout( function() {
			notice.fadeOut( function() {
				notice.remove()
			} )
		}, fadeout )
	}
}
