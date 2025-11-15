/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { DashboardHeader } from '@rank-math/components'
import App from './App'
import { getStore } from './store'
import './defaultFilters'
import './helpers'

class Analytics {
	setup() {
		getStore()
		createRoot(
			document.getElementById( 'rank-math-analytics' )
		).render(
			<>
				<DashboardHeader page="analytics" />
				<div className="wrap rank-math-wrap">
					<div className="rank-math-analytics">
						<App />
					</div>
				</div>
			</>
		)
	}
}

jQuery( document ).ready( () => {
	window.searchConsole = new Analytics()
	if ( null !== document.getElementById( 'rank-math-analytics' ) ) {
		window.searchConsole.setup()
	}
} )
