/*!
 * Rank Math - Status & Tools
 *
 * @version 1.0.33
 * @author  Rank Math
 */

;( function( $ ) {
	'use strict'

	$( function() {
		const after = $( '.rank-math-tab-nav' )

		function addNotice( msg, which, fadeout = 3000 ) {
			which = which || 'error'
			const notice = $(
				'<div class="notice notice-' +
					which +
					' is-dismissible"><p>' +
					msg +
					'</p></div>'
			).hide()

			after.prev( '.notice' ).remove()
			after.before( notice )
			notice.slideDown()
			$( 'html,body' ).animate(
				{
					scrollTop: notice.offset().top - 50,
				},
				'slow'
			)
			$( document ).trigger( 'wp-updates-notice-added' )
			if ( fadeout ) {
				setTimeout( function() {
					notice.fadeOut()
				}, fadeout )
			}
		}

		$( '.tools-action' ).on( 'click', function( event ) {
			event.preventDefault()

			const $this = $( this )
			if (
				$this.data( 'confirm' ) &&
				! confirm( $this.data( 'confirm' ) )
			) {
				return false
			}

			$this.attr( 'disabled', 'disabled' )
			$.ajax( {
				url: rankMath.api.root + 'rankmath/v1/toolsAction',
				method: 'POST',
				beforeSend( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', rankMath.restNonce )
				},
				data: {
					action: $this.data( 'action' ),
				},
			} )
				.always( function() {
					$this.removeAttr( 'disabled' )
				} )
				.fail( function( response ) {
					if ( response ) {
						if (
							response.responseJSON &&
							response.responseJSON.message
						) {
							addNotice( response.responseJSON.message )
						} else {
							addNotice( response.statusText )
						}
					}
				} )
				.done( function( response ) {
					if ( response ) {
						addNotice( response, 'success', false )
						return
					}

					addNotice( 'Something went wrong. Please try again later.' )
				} )

			return false
		} )

		$( 'input[name="enable_auto_update"]' ).on( 'change', function() {
			$( this )
				.parents( 'tr' )
				.next( 'tr.rank-math-auto-update-email' )
				.toggle( 'on' === $( this ).attr( 'value' ) )
		} )
	} )
} )( jQuery )
