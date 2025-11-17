/*
 * Rank Math settings file required for the React migration.
 *
 * @version 1.0.250
 * @author  RankMath
 */

/**
 * External Dependencies
 */
import { isNull } from 'lodash'
import { BrowserRouter } from 'react-router-dom'

/**
 * WordPress Dependencies
 */
import domReady from '@wordpress/dom-ready'
import { createRoot } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import { getStore as settingsStore } from './settings/redux/store'
import { getStore as wizardStore } from './wizard/store'
import App from './settings/App'

domReady( () => {
	// Initialize store
	settingsStore()
	wizardStore()

	const optionsPage = document.querySelector( '#rank-math-options' )
	if ( isNull( optionsPage ) ) {
		return
	}

	createRoot( optionsPage ).render(
		<BrowserRouter>
			<App />
		</BrowserRouter>
	)
} )
