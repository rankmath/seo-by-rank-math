/*!
 * Rank Math - Instant Indexing Console
 *
 * @version 0.9.0
 * @author  RankMath
 */

import ajax from '@helpers/ajax'
import addNotice from '@helpers/addNotice'

;( function( $ ) {
	// Document Ready
	$( function() {
		const urlsTextarea = $( '#bing_instant_indexing_urls' ),
			submitButton = $( '#bing_api_submit' ),
			spinner = $( '#bing_api_spinner' ),
			quotaPlaceholder = $( '#bing_api_limit' )

		const refreshQuota = function() {
			submitButton.addClass( 'disabled' )
			spinner.addClass( 'is-active' )

			ajax( 'instant_indexing_bing_get_daily_quota', {}, 'GET' )
				.always( function() {
					spinner.removeClass( 'is-active' )
				} )
				.done( function( result ) {
					let quota = 0
					if ( result.status === 'ok' && typeof( result.daily_quota ) !== 'undefined' ) {
						quota = result.daily_quota
					} else {
						addNotice(
							result.message,
							'error',
							$( '.bing-api-limit' )
						)
					}

					if ( quota ) {
						submitButton.removeClass( 'disabled' )
					}

					quotaPlaceholder.text( quota )
				} )
		}

		const initConsole = function() {

			submitButton.on( 'click', ( e ) => {
				e.preventDefault()
				let urls = urlsTextarea.val()

				submitButton.addClass( 'disabled' )
				spinner.addClass( 'is-active' )
				ajax( 'instant_indexing_bing_submit_urls', { urls }, 'POST' )
					.always( function() {
						submitButton.removeClass( 'disabled' )
						spinner.removeClass( 'is-active' )
					} )
					.done( function( result ) {
						addNotice(
							result.message,
							result.status === 'ok' ? 'success' : 'error',
							$( '.bing-api-limit' )
						)

						if ( result.status === 'ok' ) {
							refreshQuota()
						}
					} )

				return false
			} )
		}

		if ( rankMath.is_instant_indexing_configured ) {
			initConsole()
			refreshQuota()
		} else {
			spinner.removeClass( 'is-active' )
			quotaPlaceholder.text( '0' )
		}
	} )
} )( jQuery )
