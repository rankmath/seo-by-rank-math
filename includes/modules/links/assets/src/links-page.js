/**
 * Links page entry point.
 *
 * Mounts the React app on #rank-math-links-page-container.
 */

/**
 * WordPress dependencies
 */
import { createElement, createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import LinksPage from './features/links-page/LinksPage'
import './scss/links-page.scss'

const container = document.getElementById( 'rank-math-links-page-container' )
if ( container ) {
	createRoot( container ).render( createElement( LinksPage ) )
}
