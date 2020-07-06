/*!
 * Rank Math - Wizard
 *
 * @version 0.9.0
 * @author  RankMath
 */

/**
 * External Dependencies
 */
import jQuery from 'jquery'
;( function( $ ) {
	// Document Ready
	$( function() {
		window.rankMathSetupWizard = {
			init() {
				if ( rankMath.currentStep in this ) {
					this[ rankMath.currentStep ]()
				}

				$( document ).on( 'cmb_init', function() {
					$(
						'.cmb-multicheck-toggle',
						'.multicheck-checked'
					).trigger( 'click' )
				} )
			},

			compatibility() {
				$( '.wizard-deactivate-plugin' ).on( 'click', function(
					event
				) {
					event.preventDefault()

					const $this = $( this )
					if ( $this.hasClass( 'disabled' ) ) {
						return
					}

					const row = $this.closest( 'tr' )
					$.ajax( {
						url: rankMath.ajaxurl,
						type: 'POST',
						data: {
							action: 'rank_math_deactivate_plugins',
							security: rankMath.security,
							plugin: $this.data( 'plugin' ),
						},
					} ).always( function( data ) {
						if ( '1' === data ) {
							row.find( '.dashicons-warning' )
								.removeClass( 'dashicons-warning' )
								.addClass( 'dashicons-yes' )
							$this
								.addClass( 'disabled' )
								.text( rankMath.deactivated )
						}
					} )
				} )
			},

			import() {
				const importTextarea = $( '#import-progress' ),
					progressBar = $( '#import-progress-bar' )
				let width = 0,
					totalActions = 0

				const addLog = function( msg, elem ) {
					const currentdate = new Date()
					const text =
						elem.val() +
						'[' +
						( 10 > currentdate.getHours() ? '0' : '' ) +
						currentdate.getHours() +
						':' +
						( 10 > currentdate.getMinutes() ? '0' : '' ) +
						currentdate.getMinutes() +
						':' +
						( 10 > currentdate.getSeconds() ? '0' : '' ) +
						currentdate.getSeconds() +
						'] ' +
						msg +
						'\n'

					elem.text( text ).scrollTop(
						elem[ 0 ].scrollHeight - elem.height() - 20
					)
				}

				const setProgress = function( progress ) {
					if ( progress > 100 ) {
						progress = 100
					}
					progressBar.find( '.number' ).html( progress )
					progressBar
						.find( '#importBar' )
						.css( 'width', progress + '%' )
				}

				const ajaxImport = function(
					from,
					actions,
					logger,
					paged,
					callback,
					plugin
				) {
					if ( 0 === actions.length ) {
						callback()
						return
					}

					paged = paged || 1
					const action = actions.shift(),
						message =
							'deactivate' === action
								? 'Deactivating ' + plugin
								: 'Importing ' + action + ' from ' + plugin

					let actionProgress = Math.floor( 100 / totalActions )

					addLog( message, logger )
					$.ajax( {
						url: rankMath.ajaxurl,
						type: 'POST',
						data: {
							perform: action,
							pluginSlug: from,
							paged,
							action: 'rank_math_import_plugin',
							security: rankMath.security,
						},
					} )
						.success( function( result ) {
							let currentPage = 1

							if (
								result &&
								result.page &&
								result.page < result.total_pages
							) {
								currentPage = result.page + 1
								actions.unshift( action )
							}

							if ( result && result.total_pages ) {
								actionProgress = Math.ceil(
									actionProgress / result.total_pages
								)
							}
							width = width + actionProgress
							setProgress( width )
							addLog(
								result.success ? result.message : result.error,
								logger
							)
							ajaxImport(
								from,
								actions,
								logger,
								currentPage,
								callback,
								plugin
							)
						} )
						.error( function( result ) {
							addLog( result.statusText, logger )
							ajaxImport(
								from,
								actions,
								logger,
								null,
								callback,
								plugin
							)
						} )
				}

				$( '.button-import', '.form-footer' ).on( 'click', function(
					event
				) {
					event.preventDefault()
					if (
						rankMath.isConfigured &&
						! window.confirm( rankMath.confirm )
					) {
						return false
					}

					const selectedPlugins = $( '.import-data:checkbox:checked' )
					if ( ! selectedPlugins.length ) {
						window.alert( 'Please select plugin to import data.' )
						return false
					}

					const button = $( this ),
						importData = {},
						plugins = []

					$.each( selectedPlugins, function() {
						const from = $( this ).val(),
							checkboxWrap = $( this )
								.parents( '.cmb-group-description' )
								.next()
								.find( ':checkbox:checked' ),
							isPluginActive = checkboxWrap.data( 'active' ),
							plugin = $( this ).data( 'plugin' )

						plugins.push( plugin )

						const actions = $.map( checkboxWrap, function( input ) {
							return input.value
						} )
						if ( 0 < actions.length && isPluginActive ) {
							actions.push( 'deactivate' )
						}

						totalActions = totalActions + actions.length
						importData[ from ] = {
							plugin,
							actions,
						}
					} )

					button.prop( 'disabled', true )
					importTextarea.show()
					progressBar.show()
					progressBar.find( '.plugin-from' ).html( plugins.join() )
					addLog( 'Import started...', importTextarea )
					pluginsData( importData, importTextarea, function() {
						setProgress( 100 )
						button.prop( 'disabled', false )
						$( '.button', '.form-footer' ).hide()
						$( '.button-continue' ).show()
					} )
				} )

				const pluginsData = function( importData, logger, callback ) {
					const keys = Object.keys( importData ),
						length = keys.length,
						data = importData[ keys[ 0 ] ],
						from = Object.keys( importData )[ 0 ]

					delete importData[ from ]

					if ( 0 === length ) {
						addLog(
							'Import finished. Click on the button below to continue the Setup Wizard.',
							logger
						)
						callback()
						return
					}

					ajaxImport(
						from,
						data.actions,
						importTextarea,
						null,
						function() {
							pluginsData( importData, logger, callback )
						},
						data.plugin
					)
				}

				$( '.import-data' ).on( 'change', function() {
					const checkbox = $( this ),
						isChecked = this.checked,
						items = checkbox
							.parents( '.cmb-group-description' )
							.next()
							.find( '.cmb2-option' )

					for ( let i = 0; i < items.length; i++ ) {
						if ( items[ i ].type === 'checkbox' ) {
							items[ i ].checked = isChecked
						}
					}

					if ( ! isChecked ) {
						return
					}

					if ( 'yoast' === checkbox.val() ) {
						$( '.import-data[value="aioseo"]' )
							.prop( 'checked', false )
							.trigger( 'change' )

						$( '.import-data[value="seopress"]' )
							.prop( 'checked', false )
							.trigger( 'change' )
					} else if ( 'aioseo' === checkbox.val() ) {
						$( '.import-data[value="yoast"]' )
							.prop( 'checked', false )
							.trigger( 'change' )

						$( '.import-data[value="seopress"]' )
							.prop( 'checked', false )
							.trigger( 'change' )
					} else if ( 'seopress' === checkbox.val() ) {
						$( '.import-data[value="yoast"]' )
							.prop( 'checked', false )
							.trigger( 'change' )

						$( '.import-data[value="aioseo"]' )
							.prop( 'checked', false )
							.trigger( 'change' )
					}
				} )

				$( '.cmb-type-group .cmb2-checkbox-list .cmb2-option' ).on(
					'click',
					function() {
						const $this = $( this ),
							name = $this.attr( 'name' ),
							checked = $this
								.parents( 'ul' )
								.find(
									'input[name="' +
										name +
										'"]:checkbox:checked'
								),
							totalElements = $this
								.parents( 'ul' )
								.find( 'input[name="' + name + '"]' )

						if ( checked.length === totalElements.length ) {
							$this
								.parents( '.cmb-type-group' )
								.find( '.import-data' )
								.prop( 'checked', true )
								.trigger( 'change' )
						}
					}
				)

				$( '.button-deactivate-plugins' ).on( 'click', function(
					event
				) {
					const button = $( this ),
						form = button.parents( 'form' )

					if ( form.find( 'input[data-active]' ).length ) {
						event.preventDefault()
						button.text( button.data( 'deactivate-message' ) )
						$.ajax( {
							url: rankMath.ajaxurl,
							type: 'POST',
							data: {
								action: 'rank_math_deactivate_plugins',
								security: rankMath.security,
								plugin: 'all',
							},
						} )
							.success( function() {
								button.parents( 'form' ).trigger( 'submit' )
							} )
							.error( function() {
								/* eslint no-alert: 0*/
								window.alert(
									'Something went wrong! Please try again later.'
								)
							} )
					}
				} )
			},

			yoursite() {
				$( '#rank-math-search-input' ).on( 'input keypress', function(
					event
				) {
					const input = $( this ),
						link = input.next()

					if ( 13 === event.keyCode || 13 === event.which ) {
						if ( 'createEvent' in document ) {
							const doc = this.ownerDocument,
								evt = doc.createEvent( 'MouseEvents' )

							evt.initMouseEvent(
								'click',
								true,
								true,
								doc.defaultView,
								1,
								0,
								0,
								0,
								0,
								false,
								false,
								false,
								false,
								0,
								null
							)
							link[ 0 ].dispatchEvent( evt )
						}
						return false
					}

					link.attr(
						'href',
						link.data( 'href' ) + encodeURIComponent( input.val() )
					)
				} )

				const dropdown = $( '#business_type' )
				if ( 0 === parseInt( dropdown.data( 'default' ) ) ) {
					return
				}

				$( '#site_type' ).on( 'change', function() {
					const value = $( this ).val()

					if (
						'news' === value ||
						'webshop' === value ||
						'otherbusiness' === value
					) {
						dropdown.val( 'Organization' ).trigger( 'change' )
					}

					if ( 'business' === value ) {
						dropdown.val( 'LocalBusiness' ).trigger( 'change' )
					}
				} )
			},

			searchconsole() {
				$( '#console_authorization_code' ).on( 'paste', function() {
					const $this = $( this ).next( '.button' )
					setTimeout( function() {
						$this.trigger( 'click' )
					}, 100 )
				} )
			},

			ready() {
				// Enable/Disable auto-update.
				$( '#auto-update' ).on( 'change', function() {
					$( '.rank-math-auto-update-email-wrapper' ).toggle(
						$( this ).is( ':checked' )
					)
				} )

				$( '.rank-math-additional-options input.rank-math-modules' ).on(
					'change',
					function() {
						const $this = $( this )
						$.ajax( {
							url: rankMath.api.root + 'rankmath/v1/autoUpdate',
							method: 'POST',
							beforeSend( xhr ) {
								xhr.setRequestHeader(
									'X-WP-Nonce',
									rankMath.api.nonce
								)
							},
							data: {
								key: $this.data( 'key' ),
								value: $this.is( ':checked' ),
							},
						} )
					}
				)
			},
		}

		window.rankMathSetupWizard.init()
	} )
} )( jQuery )
