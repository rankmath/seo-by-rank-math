/**
 * Rank Math
 *
 * @version 0.9.0
 * @author  RankMath
 */

/**
 * External Dependencies
 */
import $ from 'jquery'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

class RankMathDashboard {
	/**
	 * Class constructor
	 */
	constructor() {
		// Click and change events.
		this.deactivatePlugins()
		this.initializeClipBoard()
		this.modeSelector()
		this.dashboardWidget()
	}

	deactivatePlugins() {
		$( '.dashboard-deactivate-plugin' ).on( 'click', function( event ) {
			event.preventDefault()

			const $this = $( this )

			$.ajax( {
				url: window.ajaxurl,
				type: 'POST',
				data: {
					action: 'rank_math_deactivate_plugins',
					security: rankMath.security,
					plugin: 'all',
				},
			} ).always( function( data ) {
				if ( '1' === data ) {
					$this.parents( '.rank-math-notice' ).remove()
				}
			} )
			return false
		} )
	}

	// Debug Report
	initializeClipBoard() {
		if ( 'undefined' === typeof ClipboardJS ) {
			return
		}

		$( '.get-debug-report' ).on( 'click', function() {
			$( '#debug-report' ).slideDown()
			$( '#debug-report textarea' )
				.trigger( 'focus' )
				.trigger( 'select' )
			$( this )
				.parent()
				.fadeOut()
			return false
		} )
		new ClipboardJS( '#copy-for-support' )
	}

	modeSelector() {
		$( '.rank-math-mode-selector a' ).on( 'click', function( e ) {
			e.preventDefault()

			const mode = $( this ).data( 'mode' )

			$.ajax( {
				url: rankMath.api.root + 'rankmath/v1/updateMode',
				method: 'POST',
				beforeSend( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', rankMath.api.nonce )
				},
				data: { mode },
			} ).done( function( response ) {
				if ( ! response ) {
					/*eslint no-alert: 0*/
					window.alert( 'Something went wrong! Please try again.' )
					return
				}

				window.location.reload()
			} )

			return false
		} )
	}

	dashboardWidget() {
		const dashboardWrapper = $( '#rank-math-dashboard-widget' )
		if ( ! dashboardWrapper.length ) {
			return
		}

		$.ajax( {
			url: rankMath.api.root + 'rankmath/v1/dashboardWidget',
			method: 'GET',
			beforeSend( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', rankMath.api.nonce )
			},
		} ).done( function( response ) {
			if ( ! response ) {
				dashboardWrapper.removeClass( 'rank-math-loading' ).html( __( 'Something went wrong! Please try again.', 'rank-math' ) )
				return
			}

			dashboardWrapper.removeClass( 'rank-math-loading' ).html( response )
		} )
	}
}

$( function() {
	new RankMathDashboard()
} )
