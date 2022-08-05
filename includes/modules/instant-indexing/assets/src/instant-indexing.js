/*!
 * Rank Math - IndexNow Console, Settings and History.
 *
 * @version 0.9.0
 * @author  RankMath
 */

import addNotice from '@helpers/addNotice'
import { toInteger } from 'lodash'

( function( $ ) {
	// Document Ready
	$( function() {
		const urlsTextarea = $( '#indexnow_urls' ),
			submitButton = $( '#indexnow_submit' ),
			spinner = $( '#indexnow_spinner' ),
			noticeLocation = $( 'div.cmb2-id-indexnow-urls' )

		let refreshHistoryInterval = null,
			refreshing = false,
			current_filter = 'all'

		const initConsole = function() {

			submitButton.on( 'click', ( e ) => {
				e.preventDefault()
				let urls = urlsTextarea.val()

				submitButton.addClass( 'disabled' )
				spinner.addClass( 'is-active' )
				$.ajax( {
					url: rankMath.indexNow.restUrl + '/submitUrls',
					type: 'POST',
					beforeSend( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', rankMath.api.nonce )
					},
					data: {
						urls: urls,
					},
					success: function( response ) {
						if ( response.success ) {
							addNotice( response.message, 'success', noticeLocation, 5000 )
							urlsTextarea.val( '' )
						} else {
							addNotice( response.message, 'error', noticeLocation, 5000 )
						}
					},
					error: function( response ) {
						let message = typeof response.responseJSON.message !== 'undefined' ? response.responseJSON.message : rankMath.indexNow.i18n.submitError
						addNotice( message, 'error', noticeLocation, 5000 )
					},
					complete: function() {
						submitButton.removeClass( 'disabled' )
						spinner.removeClass( 'is-active' )
						current_filter = 'all'
						refreshHistory()
					}
				} )
			} )
		}

		const initResponseHelper = function() {
			$( '#indexnow_show_response_codes' ).on( 'click', function( e ) {
				e.preventDefault()
				$( this ).toggleClass( 'active' )
				$( '#indexnow_response_codes' ).toggleClass( 'hidden' )
			} )
		}

		const showEmptyHistory = function() {
			let cols_num = $( '#indexnow_history' ).find( 'thead th' ).length
			$( '#indexnow_history' ).find( 'tbody' ).html( '<tr><td colspan="' + cols_num + '">' + rankMath.indexNow.i18n.noHistory + '</td></tr>' )
		}

		const initHistory = function() {
			// Clear History button.
			$( '#indexnow_clear_history' ).on( 'click', function( e ) {
				e.preventDefault()
				$.ajax( {
					url: rankMath.indexNow.restUrl + '/clearLog',
					type: 'POST',
					beforeSend( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', rankMath.api.nonce )
					},
					success: function( response ) {
						refreshHistory()
					},
					error: function( response ) {
						addNotice( rankMath.indexNow.i18n.clearHistoryError, 'error', noticeLocation, 5000 )
					},
				} )
			} )

			// Set recurring timer to refresh history.
			let interval = toInteger( rankMath.indexNow.refreshHistoryInterval )
			if ( interval > 0 ) {
				// Min. 1 second between log refreshes.
				interval = Math.max( toInteger( rankMath.indexNow.refreshHistoryInterval ), 1000 )
				refreshHistoryInterval = setInterval( refreshHistory, interval )
			}

			// Filter history.
			$( '#indexnow_history_filters a' ).on( 'click', function( e ) {
				e.preventDefault()
				let filter = $( this ).data( 'filter' )

				if ( filter === current_filter ) {
					return
				}

				current_filter = filter
				refreshHistory()
			} )

			refreshHistory()
		}

		const refreshHistory = function() {
			if ( refreshing ) {
				return
			}

			refreshing = true
			$.ajax( {
				url: rankMath.indexNow.restUrl + '/getLog',
				data: {
					filter: current_filter,
				},
				type: 'GET',
				beforeSend( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', rankMath.api.nonce )
				},
				success: function( response ) {
					$( '#indexnow_clear_history, #indexnow_history_filters' ).removeClass( 'hidden' )
					if ( 0 === response.total ) {
						$( '#indexnow_clear_history, #indexnow_history_filters' ).addClass( 'hidden' )
					}

					$( '#indexnow_history_filters a[data-filter="' + current_filter + '"]' ).addClass( 'current' ).siblings().removeClass( 'current' )
					if ( ! response.data.length ) {
						showEmptyHistory()
						return
					}

					// Build table columns: date, url, status
					let rows = ''
					response.data.forEach( function( item ) {
						rows += '<tr>'
						rows += '<td title="' + item.timeFormatted + '">' + item.timeHumanReadable + '</td>'
						rows += '<td>' + item.url + '</td>'
						rows += '<td>' + item.status + '</td>'
						rows += '</tr>'
					} )

					$( '#indexnow_history tbody' ).html( rows )
				},
				error: function( response ) {
					addNotice( rankMath.indexNow.i18n.getHistoryError, 'error', $('#indexnow_history'), 5000 )
				},
				complete: function() {
					refreshing = false
				}
			} )
		}

		const initSettings = function() {
			$('#indexnow_reset_key').on( 'click', function( e ) {
				e.preventDefault()
				let originalValue = $( '#indexnow_api_key' ).val()
				$( '#indexnow_api_key' ).val( '...' )
				$.ajax( {
					url: rankMath.indexNow.restUrl + '/resetKey',
					type: 'POST',
					beforeSend( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', rankMath.api.nonce )
					},
					success: function( response ) {
						$( '#indexnow_api_key' ).val( response.key )
						$( '#indexnow_api_key_location').text( response.location )
						$( '#indexnow_check_key' ).attr( 'href', response.location )
					},
					error: function( response ) {
						$( '#indexnow_api_key' ).val( originalValue )
					},
				} )
			} )
		}

		initConsole()
		initResponseHelper()
		initHistory()
		initSettings()
	} )
} )( jQuery )
