/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { createElement, render } from '@wordpress/element'

/**
 * Internal dependencies
 */
import App from './App'
import { getStore } from './store'
import './defaultFilters'
import './helpers'

class Analytics {
	setup() {
		getStore()
		render(
			createElement( App ),
			document.getElementById( 'rank-math-analytics' )
		)
	}
}

jQuery( document ).ready( () => {
	window.searchConsole = new Analytics()
	if ( null !== document.getElementById( 'rank-math-analytics' ) ) {
		window.searchConsole.setup()
	}
} )
