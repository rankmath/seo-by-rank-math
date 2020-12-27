/*!
 * Rank Math - Role Manager
 *
 * @version 1.0.55
 * @author  Rank Math
 */

/**
 * External Dependencies
 */
import JQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

JQuery( function() {
	JQuery( '.reset-options' ).on( 'click', function() {
		if (
			// eslint-disable-next-line no-alert
			window.confirm(
				__( 'Are you sure? You want to reset settings.', 'rank-math' )
			)
		) {
			JQuery( window ).off( 'beforeunload' )
			return true
		}

		return false
	} )
} )
