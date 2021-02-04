/**
 * Rank Math - Version Control
 *
 * @author  Rank Math
 */
( function( $ ) {
	'use strict'

	$( function() {
		$( '.rank-math-rollback-form' ).submit( function( event ) {
			if ( ! confirm( rankMath.rollbackConfirm.replace( '%s', $( '#rm_rollback_version' ).val() ) ) ) {
				return false
			}
			$( '#rm-rollback-button' ).prop( 'disabled', true )
			$( '.rollback-loading-indicator' ).removeClass( 'hidden' )
		} )

		const $rollback_button = $( '#rm-rollback-button' )

		$( '#rm_rollback_version' ).change( function() {
			$rollback_button.text( $rollback_button.data( 'buttonlabel' ).replace( '%s', $( this ).val() ) )
		} ).change()

		$( 'input[name="enable_auto_update"]' ).on( 'change', function() {
			$( '#control_update_notification_email' )
				.toggleClass( 'hidden', 'on' === $( this ).attr( 'value' ) )
		} ).filter( ':checked' ).trigger( 'change' )
	} )
}( jQuery ) )
