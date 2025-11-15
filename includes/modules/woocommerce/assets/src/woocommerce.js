/*global woocommerce_admin_meta_boxes,accounting*/

/**
 * External dependencies
 */
import $ from 'jquery'
import { debounce, isUndefined, has } from 'lodash'

/**
 * WordPress dependencies
 */
import { doAction, addFilter } from '@wordpress/hooks'
import { dispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'

/**
 * RankMath custom fields integration class
 */
class RankMathProductDescription {
	constructor() {
		this.excerpt = $( '#excerpt' )
		this.elemPrice = $( '#_sale_price' )
		this.elemRegPrice = $( '#_regular_price' )
		this._data = { excerpt: false }

		// Refresh functions.
		this.refreshWCPrice = this.refreshWCPrice.bind( this )
		this.events = this.events.bind( this )
		this.ensureIframe = this.ensureIframe.bind( this )
		this.events()
		this.hooks()
	}

	/**
	 * Hook into Rank Math App eco-system
	 */
	hooks() {
		if ( undefined === this.excerpt ) {
			return
		}
		addFilter( 'rank_math_content', 'rank-math', this.getContent.bind( this ), 11 )
		addFilter( 'rank_math_dataCollector_data', 'rank-math', this.syncExcerpt.bind( this ), 11 )
	}

	/**
	 * Gather custom fields data for analysis
	 *
	 * @param {string} content Content
	 *
	 * @return {string} New content
	 */
	getContent( content ) {
		content += ( 'undefined' !== typeof tinymce && tinymce.activeEditor && 'excerpt' === tinymce.activeEditor.id ) ? tinymce.activeEditor.getContent() : this.excerpt.val()
		return content
	}

	/**
	 * Syncs Excerpt value with dataCollectors _data ie the `rankMathEditor.assessor.dataCollector.getData()`.
	 *
	 * @param {object|string} data The dataCollectors _data
	 *
	 * @return {object|string} synced data.
	 */
	syncExcerpt( data ) {
		return ! has( data, 'excerpt' ) || false === this._data.excerpt ? data : { ...data, excerpt: this._data.excerpt }
	}

	/**
	 * Capture events from custom fields to refresh Rank Math analysis
	 */
	events() {
		if ( 'undefined' !== typeof tinymce && tinymce.activeEditor && 'undefined' !== typeof tinymce.editors.excerpt ) {
			tinymce.editors.excerpt.on( 'keyup change', debounce( () => {
				rankMathEditor.refresh( 'content' )
			}, 500 ) )
		}

		tinymce.on( 'AddEditor', this.ensureIframe )

		// WC Events
		this.debounceWCPrice = debounce( this.refreshWCPrice, 500 )
		this.elemPrice.on( 'input', this.debounceWCPrice )
		this.elemRegPrice.on( 'input', this.debounceWCPrice )

		this.ensureIframe()

		if ( isGutenbergAvailable() ) {
			this.excerpt.on( 'input', debounce( ( event ) => {
				this._data.excerpt = event.currentTarget.value
				dispatch( 'rank-math' ).updateSerpDescription( this._data.excerpt )
			}, 500 )
			)
		}
	}

	refreshWCPrice() {
		swapVariables.setVariable( 'wc_price', this.getWooCommerceProductPrice() )
		doAction( 'rank_math_update_description_preview' )
	}

	getWooCommerceProductPrice() {
		const price = this.elemPrice.val() ? this.elemPrice.val() : this.elemRegPrice.val()
		return accounting.formatMoney( price, {
			symbol: woocommerce_admin_meta_boxes.currency_format_symbol,
			decimal: woocommerce_admin_meta_boxes.currency_format_decimal_sep,
			thousand: woocommerce_admin_meta_boxes.currency_format_thousand_sep,
			precision: woocommerce_admin_meta_boxes.currency_format_num_decimals,
			format: woocommerce_admin_meta_boxes.currency_format,
		} )
	}

	/**
	 * Ensures events for the excerpt editor are set.
	 */
	ensureIframe() {
		if ( tinymce.editors && ! isUndefined( tinymce.editors.excerpt ) ) {
			tinymce.editors.excerpt.on(
				'keyup change',
				debounce( () => {
					this._data.excerpt = tinymce.get( 'excerpt' ).getContent()
					dispatch( 'rank-math' ).updateSerpDescription( this._data.excerpt )
				}, 500 )
			)
		}
	}
}

$( function() {
	new RankMathProductDescription()
} )
