/**
 * External Dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress Dependencies
 */
import { createRoot } from '@wordpress/element'
import domReady from '@wordpress/dom-ready'

/**
 * Internal Dependencies
 */
import App from './App'
import { getStore } from './store'
import ajax from '@helpers/ajax'

/**
 * Initialize SEO Analysis store
 */
getStore()

/**
 * Render SEO Analyzer page
 */
domReady( () => {
	const root = createRoot( document.getElementById( 'rank-math-seo-analysis-wrapper' ) )

	root.render( <App /> )

	jQuery( document ).on( 'click', '.enable-auto-update', function( event ) {
		event.preventDefault()

		ajax( 'enable_auto_update' )
		jQuery( this ).closest( '.auto-update-disabled' )
			.addClass( 'hidden' )
			.siblings( '.auto-update-enabled' )
			.removeClass( 'hidden' )
			.closest( '.row-description' )
			.find( '.status-icon' )
			.removeClass( 'status-warning dashicons-warning' )
			.addClass( 'status-ok dashicons-yes' )
	} )
} )
