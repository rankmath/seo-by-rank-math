/**
 * External dependencies
 */
import jQuery from 'jquery'

/*!
* Rank Math
*
* @version 0.9.0
* @author  RankMath
*/
( function( $ ) {
	'use strict'

	// Document Ready
	$( function() {
		window.rankMathFront = {

			init() {
				this.adminMenu()
			},

			adminMenu() {
				const menu = $( '#wp-admin-bar-rank-math-mark-me' ),
					self = this,
					icon = '<span class="dashicons dashicons-yes" style="font-family:dashicons;font-size:19px;line-height:28px;margin:0 3px 0 -4px;"></span>'

				menu.on( 'click', '.mark-page-as a', function( event ) {
					event.preventDefault()
					self.ajax( 'mark_page_as', {
						objectID: rankMath.objectID,
						objectType: rankMath.objectType,
						what: $( this ).attr( 'href' ).replace( '#', '' ),
					} )

					if ( $( this ).find( '.dashicons' ).length ) {
						$( this ).find( '.dashicons' ).remove()
					} else {
						$( this ).prepend( icon )
					}
				} )
			},

			ajax( action, data, method ) {
				return $.ajax( {
					url: rankMath.ajaxurl,
					type: method || 'POST',
					dataType: 'json',
					data: $.extend( true, {
						action: 'rank_math_' + action,
						security: rankMath.security,
					}, data ),
				} )
			},
		}

		window.rankMathFront.init()
	} )
}( jQuery ) )
