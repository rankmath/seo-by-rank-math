/**
 * External dependencies
 */
import jQuery from 'jquery'
import { debounce } from 'lodash'

/**
 * WordPress dependencies
 */
import { addAction, addFilter } from '@wordpress/hooks'

class GlueCustomFields {
	/**
	 * Field values
	 *
	 * @type {Array}
	 */
	fields = []

	constructor() {
		addAction( 'rank_math_loaded', 'rank-math', this.init.bind( this ) )
	}

	init() {
		this.getFields()
		this.events()
	}

	/**
	 * Get custom fields ids
	 */
	getFields() {
		jQuery( '#the-list > tr:visible' ).each( ( s, e ) => {
			const key = jQuery( '#' + e.id + '-key' ).val()

			if ( -1 !== jQuery.inArray( key, rankMath.analyzeFields ) ) {
				this.fields.push( '#' + e.id + '-value' )
			}
		} )
	}

	/**
	 * Capture events from custom fields to refresh Rank Math analysis
	 */
	events() {
		// Hook into rank math
		addFilter(
			'rank_math_content',
			'rank-math',
			this.getContent.bind( this )
		)

		// Change event
		jQuery( this.fields ).each( ( key, value ) => {
			jQuery( value ).on(
				'keyup change',
				debounce( function() {
					rankMathEditor.refresh( 'content' )
				}, 500 )
			)
		} )
	}

	/**
	 * Gather custom fields data for analysis
	 *
	 * @param {string} content Content to analyze.
	 *
	 * @return {string} New content
	 */
	getContent( content ) {
		jQuery( this.fields ).each( ( key, value ) => {
			content += jQuery( value ).val()
		} )

		return content
	}
}

new GlueCustomFields()
