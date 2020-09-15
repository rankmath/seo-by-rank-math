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

class RankMathDashboard {
	/**
	 * Class constructor
	 */
	constructor() {
		this.deactivatePlugins()
		this.updateModules()
		this.initializeClipBoard()
		this.modeSelector()
		this.usageTracking()
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

	// Enable/Disable Modules
	updateModules() {
		$( '.rank-math-modules' ).on( 'change', function() {
			const button = $( this ),
				box = button.closest( '.rank-math-box' ),
				isChecked = button.is( ':checked' ),
				value = button.val()

			box.addClass( 'saving' )
			$.ajax( {
				url: rankMath.api.root + 'rankmath/v1/saveModule',
				method: 'POST',
				beforeSend( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', rankMath.api.nonce )
				},
				data: {
					module: value,
					state: isChecked ? 'on' : 'off',
				},
			} ).done( function( response ) {
				if ( ! response ) {
					/*eslint no-alert: 0*/
					window.alert( 'Something went wrong! Please try again.' )
					return
				}

				box.removeClass( 'saving' )
				box.toggleClass( 'active', isChecked )

				// Reload menu
				$.ajax( {
					url: window.location.pathname + window.location.search,
					method: 'GET',
				} ).done( function( responseMenu ) {
					if ( responseMenu ) {
						const incoming = $( responseMenu ).find(
							'#toplevel_page_rank-math'
						)
						const current = $(
							'#toplevel_page_rank-math > .wp-submenu'
						)
						if (
							incoming.length &&
							incoming.find( '> .wp-submenu > li' ).length !==
								current.children( 'li' ).length
						) {
							current.fadeOut( 200, function() {
								current
									.html(
										incoming
											.find( '> .wp-submenu' )
											.hide()
											.children()
									)
									.fadeIn( 400 )
							} )
						}
					}
				} )
			} )
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
				.focus()
				.select()
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

	usageTracking() {
		$( '#rank-math-usage-tracking' ).on( 'change', function() {
			$.ajax( {
				url: rankMath.api.root + 'rankmath/v1/updateTracking',
				method: 'POST',
				beforeSend( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', rankMath.api.nonce )
				},
				data: {
					enable: $( this ).is( ':checked' ),
				},
			} ).done( function( response ) {
				if ( ! response ) {
					/*eslint no-alert: 0*/
					window.alert( 'Something went wrong! Please try again.' )
				}
			} )
		} )
	}
}

jQuery( document ).ready( () => {
	new RankMathDashboard()
} )
