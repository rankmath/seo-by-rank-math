/**
 * External Dependencies
 */
import { BrowserRouter } from 'react-router-dom'

/**
 * WordPress Dependencies
 */
import domReady from '@wordpress/dom-ready'
import { createRoot } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import VersionControlApp from './VersionControlApp'
import { getStore } from './store'
import StatusAndToolsPage from './StatusAndToolsPage'

/*!
 * Rank Math - Status & Tools
 *
 * @version 1.0.33
 * @author  Rank Math
 */
domReady( () => {
	// Initialize store
	getStore()

	const root = document.getElementById( 'rank-math-tools-wrapper' )
	if ( root ) {
		createRoot( root ).render(
			<BrowserRouter>
				<StatusAndToolsPage />
			</BrowserRouter>
		)
	}

	const versionControlWrapper = document.getElementById( 'rank-math-version-control-wrapper' )
	if ( versionControlWrapper ) {
		createRoot( versionControlWrapper ).render(
			<VersionControlApp />
		)
	}
} )
