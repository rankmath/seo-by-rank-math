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

class RankMathPostList {
	/**
	 * Class constructor
	 */
	constructor() {
		this.addButtons()
		this.editingEvents()
		this.saveEvents()
	}

	addButtons() {
		const headers = jQuery(
			'#rank_math_seo_details, #rank_math_title, #rank_math_description, #rank_math_image_alt, #rank_math_image_title'
		)
		headers.each( function() {
			const $this = jQuery( this )

			$this.append(
				' <a href=#" class="dashicons dashicons-edit" title="' +
					rankMath.bulkEditTitle +
					'"></a>'
			)
			$this.wrapInner( '<span/>' )
			$this.append(
				' <span><a href="#" class="button button-primary button-small rank-math-column-save-all">' +
					rankMath.buttonSaveAll +
					'</a> <a href="#" class="button-link button-link-delete rank-math-column-cancel-all">' +
					rankMath.buttonCancel +
					'</a></span>'
			)
		} )

		headers.on(
			'click',
			'.dashicons-edit, .rank-math-column-cancel-all',
			function( event ) {
				event.preventDefault()
				const $this = jQuery( this ).closest( 'th' )

				if (
					jQuery( this ).hasClass( 'rank-math-column-cancel-all' )
				) {
					headers.removeClass( 'bulk-editing' )
					jQuery(
						'.rank-math-column-cancel',
						'td.bulk-editing.dirty'
					).trigger( 'click' )
					jQuery( 'td.bulk-editing' ).removeClass( 'bulk-editing' )
				} else {
					$this.toggleClass( 'bulk-editing' )
					jQuery( 'td.column-' + $this.attr( 'id' ) ).toggleClass(
						'bulk-editing'
					)
				}
			}
		)
	}

	editingEvents() {
		jQuery( '.rank-math-column-value' )
			.on( 'input', function() {
				const $this = jQuery( this )
				const td = $this.closest( 'td' )
				const val = $this.text()

				if ( val !== $this.prev().text() ) {
					td.addClass( 'dirty' )
				} else {
					td.removeClass( 'dirty' )
				}
			} )
			.on( 'keypress', function( event ) {
				if ( 13 === event.keyCode ) {
					jQuery( this )
						.parent()
						.find( '.rank-math-column-save' )
						.trigger( 'click' )
					return false
				}
			} )

		jQuery( '.rank-math-column-cancel' ).on( 'click', function( event ) {
			event.preventDefault()
			const $this = jQuery( this ).closest( 'td' )

			$this.removeClass( 'dirty' )
			let display = $this
				.find( '.rank-math-column-value' )
				.prev( '.rank-math-column-display' )

			if ( display.find( 'span' ).length ) {
				display = display.find( 'span' )
			}
			$this.find( '.rank-math-column-value' ).text( display.text() )
		} )
	}

	saveEvents() {
		const self = this

		jQuery( '.rank-math-column-save-all' ).on( 'click', function( event ) {
			event.preventDefault()

			const $this = jQuery( this )
			const data = {}
			const columns = []

			jQuery( '.dirty.bulk-editing' ).each( function() {
				const column = jQuery( this )
				const postID = parseInt(
					column
						.closest( 'tr' )
						.attr( 'id' )
						.replace( 'post-', '' )
				)
				const valueField = column.find( '.rank-math-column-value' )

				columns.push( column )
				data[ postID ] = data[ postID ] || {}
				data[ postID ][ valueField.data( 'field' ) ] = valueField.text()
			} )

			self.save( data ).done( function( results ) {
				if ( results.success ) {
					columns.forEach( function( column ) {
						self.setColumn( column )
					} )

					jQuery( $this.next() ).trigger( 'click' )
				}
			} )
		} )

		jQuery( '.rank-math-column-save' ).on( 'click', function( event ) {
			event.preventDefault()

			const column = jQuery( this ).closest( '.dirty' )
			const postID = parseInt(
				column
					.closest( 'tr' )
					.attr( 'id' )
					.replace( 'post-', '' )
			)
			const valueField = column.find( '.rank-math-column-value' )

			const data = {}
			data[ postID ] = {}
			data[ postID ][ valueField.data( 'field' ) ] = valueField.text()

			self.save( data ).done( function( results ) {
				if ( results.success ) {
					self.setColumn( column )
				}
			} )
		} )
	}

	setColumn( column ) {
		column.removeClass( 'dirty bulk-editing' )
		let display = column
			.find( '.rank-math-column-value' )
			.prev( '.rank-math-column-display' )

		if ( display.find( 'span' ).length ) {
			display = display.find( 'span' )
		}

		const node = document.createElement( 'div' )
		node.innerHTML = column.find( '.rank-math-column-value' ).text()
		display.text( node.innerText )
	}

	save( data ) {
		return jQuery.ajax( {
			url: rankMath.endpoint + '/updateMetaBulk',
			method: 'POST',
			beforeSend( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', rankMath.restNonce )
			},
			data: {
				rows: data,
			},
		} )
	}
}

jQuery( function() {
	new RankMathPostList()
} )
