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
import { isNull, isUndefined, includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Modal } from '@wordpress/components'
import { render } from '@wordpress/element'

class RankMathDashboard {
	/**
	 * Class constructor
	 */
	constructor() {
		// Click and change events.
		this.deactivatePlugins()
		this.updateModules()
		this.initializeClipBoard()
		this.modeSelector()
		this.dashboardWidget()
		this.onsiteCheckout()
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
		$( '.rank-math-box.is-pro' ).on( 'click', function( e ) {
			e.preventDefault()
			window.open( '//rankmath.com/pricing/?utm_source=Plugin&utm_medium=Unlock%20PRO%20Module%20Box&utm_campaign=WP' )
			return false
		} )

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
						const incoming = $( responseMenu ).find( '#toplevel_page_rank-math' )
						const current = $( '#toplevel_page_rank-math > .wp-submenu' )
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

				// Check module dependencies.
				$( '.rank-math-modules' ).each( function() {
					const $this = $( this )
					const deps = $this.data( 'depmodules' )

					if ( typeof deps === 'object' && deps.length ) {
						const depsEnabled = deps.filter( function( dep ) {
							return ! $( '#module-' + dep ).is( ':checked' )
						} )

						const disabled = depsEnabled.length > 0
						$this.prop( 'disabled', disabled )
						if ( disabled ) {
							$this.closest( '.rank-math-box' ).removeClass( 'active' )
						} else if ( $this.is( ':checked' ) ) {
							$this.closest( '.rank-math-box' ).addClass( 'active' )
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

	onsiteCheckout() {
		$( '.pro-link' ).on( 'click', function( e ) {
			const url = $( this ).data( 'url' )
			if ( typeof url === 'undefined' || url === '' ) {
				return
			}

			e.preventDefault()
			if ( ! isNull( document.getElementById( 'rank-math-onsite-checkout-wrapper' ) ) ) {
				$( '.components-modal__screen-overlay' ).show()
				return false
			}

			$( 'body' ).append( '<div id="rank-math-onsite-checkout-wrapper"></div>' )
			setTimeout( () => {
				render(
					<Modal
						className="rank-math-onsite-checkout-modal"
						onRequestClose={ ( event ) => {
							if ( ! isUndefined( event ) && includes( event.target.classList, 'rank-math-onsite-checkout-modal' ) ) {
								return false
							}

							$( '.components-modal__screen-overlay' ).hide()
							$( 'body' ).removeClass( 'modal-open' )
						} }
						shouldCloseOnClickOutside={ true }
					>
						<iframe width="100%" height="100%" src={ url } />
					</Modal>,
					document.getElementById( 'rank-math-onsite-checkout-wrapper' )
				)
			}, 100 )

			return false
		} )
	}
}

$( function() {
	new RankMathDashboard()
} )
