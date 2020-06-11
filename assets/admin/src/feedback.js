/*!
 * Rank Math
 *
 * @version 0.9.0
 * @author  RankMath
 */

/**
 * External Dependencies
 */
import jQuery from 'jquery'
;( function( $ ) {
	// Document Ready
	$( function() {
		const dialog = $( '#rank-math-feedback-form' ),
			reasonsWrapper = dialog.find(
				'.rank-math-feedback-options-wrapper'
			),
			reasons = dialog.find( '.rank-math-feedback-input-wrapper' ),
			dialogForm = dialog.find( 'form' ),
			deactivateLink = $( '#the-list' ).find(
				'[data-slug="seo-by-rank-math"] span.deactivate a'
			)

		deactivateLink.on( 'click', function( event ) {
			event.preventDefault()
			dialog.fadeIn()
		} )

		dialog.on( 'change', 'input:radio', function() {
			reasons.removeClass( 'checked' )
			$( this )
				.parent()
				.toggleClass( 'checked' )

			if (
				$( this )
					.parent()
					.find( '.regular-text' ).length
			) {
				reasonsWrapper.addClass( 'selected' )
			} else {
				reasonsWrapper.removeClass( 'selected' )
			}

			dialogForm.find( '.button-submit' ).removeAttr( 'disabled' )
		} )

		dialog.on( 'click', '.button-skip', function() {
			window.location.href = deactivateLink.attr( 'href' )
		} )

		dialog.on( 'click', '.button-close', function( event ) {
			event.preventDefault()
			dialog.fadeOut()
		} )

		dialog.on( 'click', function( e ) {
			if ( 'rank-math-feedback-form' === e.target.id ) {
				$( this )
					.find( '.button-close' )
					.trigger( 'click' )
			}
		} )

		dialogForm.on( 'submit', function( event ) {
			event.preventDefault()

			dialogForm
				.find( '.button-submit' )
				.text( '' )
				.addClass( 'loading' )

			$.ajax( {
				url: window.ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: dialogForm.serialize(),
			} ).done( function() {
				window.location.href = deactivateLink.attr( 'href' )
			} )
		} )
	} )
} )( jQuery )
