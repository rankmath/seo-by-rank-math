/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * Add notice helper
 *
 * @param {mixed} elem jQuery object or selector.
 */
export default function( elem ) {
	let theElem = elem || '.rank-math-box-tabs'
	jQuery( theElem ).children().on( 'click', function( e ) {
		e.preventDefault();
		var $this = jQuery( this );
		var target = $this.attr( 'href' ).substr( 1 );
		$this.addClass( 'active-tab' ).siblings().removeClass( 'active-tab' );
		jQuery( '#'+target ).addClass( 'active-tab' ).siblings().removeClass( 'active-tab' );
	});
}
