/**
 * External Dependencies
 */
import jQuery from 'jquery'

/**
 * Rank Math - Version Control
 *
 * @author  Rank Math
 */
( function( $ ) {
	'use strict'
	// Document Ready
	$( function() {
		$( '.rank-math-rollback-form' ).on( 'submit', function() {
			if ( ! confirm( rankMath.rollbackConfirm.replace( '%s', $( '#rm_rollback_version' ).val() ) ) ) {
				return false
			}
			$( '#rm-rollback-button' ).prop( 'disabled', true )
			$( '.rollback-loading-indicator' ).removeClass( 'hidden' )
		} )

		const rollbackButton = $( '#rm-rollback-button' )

		$( '#rm_rollback_version' ).on( 'change', function() {
			rollbackButton.text( rollbackButton.data( 'buttonlabel' ).replace( '%s', $( this ).val() ) )
		} ).trigger( 'change' )

		$( 'input[name="enable_auto_update"]' ).on( 'change', function() {
			$( '#control_update_notification_email' )
				.toggleClass( 'hidden', 'on' === $( this ).attr( 'value' ) )
		} ).filter( ':checked' ).trigger( 'change' )
	} )
}( jQuery ) )
