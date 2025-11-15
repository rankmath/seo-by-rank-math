/*!
 * Rank Math - Wizard
 *
 * @version 0.9.0
 * @author  RankMath
 */

/**
 * External dependencies
 */
import { BrowserRouter } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready'
import { createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Header from './wizard/views/Header'
import App from './wizard/App'
import { getStore } from './wizard/store'

domReady( () => {
	// Initialize store
	getStore()

	const root = createRoot(
		document.getElementById( 'rank-math-wizard-wrapper' )
	)

	root.render(
		<>
			<Header />
			<BrowserRouter>
				<App />
			</BrowserRouter>
		</>
	)
} )
