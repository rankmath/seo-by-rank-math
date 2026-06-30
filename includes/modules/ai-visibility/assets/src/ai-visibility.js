/**
 * AI Visibility — entry point.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import App from './App'
import './App.scss'

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'rank-math-ai-visibility-container' )
	if ( ! container ) {
		return
	}

	const config = window.rankMath ?? {}
	createRoot( container ).render( <App config={ config } /> )
} )
