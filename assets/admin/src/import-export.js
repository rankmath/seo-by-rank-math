/*!
 * Rank Math
 *
 * @version 0.9.0
 * @author  RankMath
 */

/**
 * External Dependencies
 */
import jQuery from 'jquery'

/**
 * Internal Dependencies
 */
import ajax from '@helpers/ajax'
import addNotice from '@helpers/addNotice'
import boxTabs from '@helpers/boxTabs'

/*eslint no-alert: 0*/
( function( $ ) {
	// Document Ready
	$( function() {
		const rankMathImportExport = {
			init() {
				boxTabs()

				$( document.body ).on(
					'click',
					'.rank-math-action',
					( event ) => {
						const button = $( event.currentTarget ),
							action = button.data( 'action' )

						if ( action in this ) {
							this[ action ]( event )
						}
					}
				)
				this.importConfirm()
			},

			importConfirm() {
				const fileInput = $( '#import-me' )

				fileInput.on( 'change', function() {
					fileInput.removeClass( 'invalid' )
				} )

				$( '#rank-math-import-form' ).on( 'submit', function( event ) {
					if ( ! fileInput.get( 0 ).files.length ) {
						fileInput.addClass( 'invalid' )
						event.preventDefault()
						return
					}

					if ( ! window.confirm( rankMath.importSettingsConfirm ) ) {
						event.preventDefault()
					}
				} )
			},

			createBackup( event ) {
				const button = $( event.currentTarget )

				button.prop( 'disabled', true )

				ajax( 'create_backup' )
					.always( function() {
						button.prop( 'disabled', false )
					} )
					.done( function( response ) {
						if ( ! response.success ) {
							addNotice(
								response.error,
								'error',
								$( '.wp-header-end' ),
								2000
							)
						} else {
							const table = button.parent().find( 'tbody' ),
								clone = table.find( 'tr:first' ).clone()

							clone
								.removeClass( 'hidden' )
								.find( 'th' )
								.html( response.backup )
							clone
								.find( '[data-action]' )
								.attr( 'data-key', response.key )
							table.prepend( clone )
							$( '#rank-math-no-backup-message' ).addClass(
								'hidden'
							)
							addNotice(
								response.message,
								'success',
								$( '.wp-header-end' ),
								2000
							)
						}
					} )
			},

			restoreBackup( event ) {
				if ( ! window.confirm( rankMath.restoreConfirm ) ) {
					return
				}

				const button = $( event.currentTarget )

				button.prop( 'disabled', true )

				ajax( 'restore_backup', { key: button.attr( 'data-key' ) } )
					.always( function() {
						button.prop( 'disabled', false )
					} )
					.done( function( response ) {
						if ( ! response.success ) {
							addNotice(
								response.error,
								'error',
								$( '.wp-header-end' ),
								2000
							)
						} else {
							addNotice(
								response.message,
								'success',
								$( '.wp-header-end' ),
								2000
							)
						}
					} )
			},

			deleteBackup( event ) {
				if ( ! window.confirm( rankMath.deleteBackupConfirm ) ) {
					return
				}

				const button = $( event.currentTarget )

				button.prop( 'disabled', true )

				ajax( 'delete_backup', { key: button.data( 'key' ) } )
					.always( function() {
						button.prop( 'disabled', false )
					} )
					.done( function( response ) {
						if ( ! response.success ) {
							addNotice(
								response.error,
								'error',
								$( '.wp-header-end' ),
								2000
							)
							return
						}

						const table = button.closest( 'table' )
						button.closest( 'tr' ).fadeOut( function() {
							$( this ).remove()

							if ( 1 > table.find( 'tr' ).length ) {
								$( '#rank-math-no-backup-message' ).show()
							}
						} )
						addNotice(
							response.message,
							'success',
							$( '.wp-header-end' ),
							2000
						)
					} )
			},

			getAllActions() {
				let actions = $.map(
					$( '.import-plugins .active-tab' )
						.find( '.choices' )
						.find( 'input:checkbox:checked' ),
					function( input ) {
						return input.value
					}
				)

				/**
				 * If it contains "recalculate", then move that to the beginning, or after "postmeta" if it exists.
				 * The recalculate action opens a new window, and if we wait too long after user interaction then the browser will block it.
				 * But it also has to be after postmeta, because postmeta is required for recalculate to work.
				 */
				if ( -1 !== actions.indexOf( 'recalculate' ) ) {
					actions = actions.filter( function( action ) {
						return 'recalculate' !== action
					} )
					const index = actions.indexOf( 'postmeta' )
					if ( -1 !== index ) {
						actions.splice( index + 1, 0, 'recalculate' )
					} else {
						actions.unshift( 'recalculate' )
					}
				}

				return actions
			},

			importPlugin( event ) {
				const button = $( event.currentTarget )

				const selectedPlugin = button.closest( 'form' ).find( '.rank-math-box-tabs > .active-tab' ).text().trim()
				if ( ! window.confirm( rankMath.importPluginConfirm.replace( '%s', selectedPlugin ) ) ) {
					return
				}

				const actions = this.getAllActions()
				if ( 1 > actions.length ) {
					addNotice(
						rankMath.importPluginSelectAction,
						'error',
						$( '.wp-header-end' ),
						5000
					)
					return
				}

				button.prop( 'disabled', true )

				if ( button.data( 'active' ) ) {
					actions.push( 'deactivate' )
				}

				const importTextarea = $(
					'<textarea id="import-progress-area" class="import-progress-area large-text" disabled="disabled" rows="8" style="margin: 20px 0;background: #eee;"></textarea>'
				)

				$( '#import-progress-area' ).remove()
				button.parents( '.active-tab' ).find( 'table' ).after( importTextarea )
				this.addLog( 'Import started...', importTextarea )
				this.ajaxImport(
					button.data( 'slug' ),
					actions,
					importTextarea,
					null,
					function() {
						button.prop( 'disabled', false )
						setTimeout( function() {
							importTextarea.fadeOut( function() {
								importTextarea.remove()
							} )
						}, 10000 )
					}
				)
			},

			ajaxImport( slug, actions, logger, paged, callback ) {
				if ( 0 === actions.length ) {
					let message = 'Import finished.'
					// If "recalculate" is in getAllActions, then we need to show a different message to the user.
					if ( -1 !== this.getAllActions().indexOf( 'recalculate' ) ) {
						message = 'Import finished. You can continue while we recalculate your SEO scores.'
					}

					this.addLog( message, logger )
					callback()
					return
				}

				const action = actions.shift(),
					message =
						'deactivate' === action
							? 'Deactivating plugin'
							: 'Importing ' + action

				paged = paged || 1

				if ( 'recalculate' === action ) {
					this.addLog( 'Starting SEO Score Recalculation...', logger )
					this.startRecalculation( logger, callback )
					this.ajaxImport( slug, actions, logger, null, callback )
					return
				}

				this.addLog( message, logger )
				ajax( 'import_plugin', {
					perform: action,
					pluginSlug: slug,
					paged,
				} )
					.done( ( result ) => {
						paged = 1
						if (
							result &&
							result.page &&
							result.page < result.total_pages
						) {
							paged = result.page + 1
							actions.unshift( action )
						}

						this.addLog(
							result.success ? result.message : result.error,
							logger
						)
						this.ajaxImport(
							slug,
							actions,
							logger,
							paged,
							callback
						)
					} )
					.fail( function( result ) {
						this.addLog( result.statusText, logger )
						this.ajaxImport( slug, actions, logger, null, callback )
					} )
			},

			addLog( msg, elem ) {
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
			},

			cleanPlugin( event ) {
				const button = $( event.currentTarget )

				const selectedPlugin = button.closest( 'form' ).find( '.rank-math-box-tabs > .active-tab' ).text().trim()
				if ( ! window.confirm( rankMath.cleanPluginConfirm.replace( '%s', selectedPlugin ) ) ) {
					return
				}

				button.prop( 'disabled', true )

				ajax( 'clean_plugin', { pluginSlug: button.data( 'slug' ) } )
					.always( function() {
						button.prop( 'disabled', false )
						$( 'html, body' ).animate( { scrollTop: 0 }, 'fast' );
					} )
					.done( function( response ) {
						if ( response.success ) {
							button.closest( 'tr' ).fadeOut( function() {
								$( this ).remove()
							} )
						}
						addNotice(
							response.success
								? response.message
								: response.error,
							response.success ? 'success' : 'error',
							$( '.wp-header-end' ),
							5000
						)
					} )
			},

			startRecalculation( logger, callback ) {
				// Start recalculation in a new small window.
				const recalcWindow = window.open(
					rankMath.recalculateURL,
					'rank-math-recalculation',
					'width=530,height=300'
				)

				// Check if the window is open.
				if ( null === recalcWindow ) {
					$( 'input[value="recalculate"]' ).prop( 'checked', false ).next( 'label' ).find( 'a' ).removeClass( 'hidden' )
					this.addLog( 'The SEO Score Recalculation could not be started because the popup was blocked. Try again with the above link, or run it from Rank Math > Status & Tools > Database Tools.', logger )
					callback()
				}
			},
		}

		rankMathImportExport.init()
	} )
}( jQuery ) )
