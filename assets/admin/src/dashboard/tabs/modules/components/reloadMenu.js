/**
 * External dependencies
 */
import $ from 'jquery'

export default () => {
	// Reload menu
	$.ajax( {
		url: window.location.pathname + window.location.search,
		method: 'GET',
	} ).done( function( responseMenu ) {
		if ( responseMenu ) {
			const incoming = $( responseMenu ).find( '#toplevel_page_rank-math' )
			const current = $( '#toplevel_page_rank-math > .wp-submenu' )
			if (
				incoming.length &&
				incoming.find( '> .wp-submenu > li' ).length !==
					current.children( 'li' ).length
			) {
				current.fadeOut( 200, function() {
					current
						.html( incoming.find( '> .wp-submenu' ).hide().children() )
						.fadeIn( 400 )
				} )
			}
		}
	} )
}
