/*global rankMathEditor, tinymce, accounting*/

/**
 * External dependencies
 */
import $ from 'jquery'
import debounce from 'lodash/debounce'

/**
 * WordPress dependencies
 */
import { doAction, addFilter } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'

/**
 * RankMath custom fields integration class
 */
class RankMathProductDescription {
	constructor() {
		this.excerpt = $( '#excerpt' )
		this.elemPrice = $( '#_sale_price' )
		this.elemRegPrice = $( '#_regular_price' )

		// Refresh functions.
		this.refreshWCPrice = this.refreshWCPrice.bind( this )
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
		addFilter( 'rank_math_content', 'rank-math', this.getContent.bind( this ) )
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
	 * Capture events from custom fields to refresh Rank Math analysis
	 */
	events() {
		if ( 'undefined' !== typeof tinymce && tinymce.activeEditor && 'undefined' !== typeof tinymce.editors.excerpt ) {
			tinymce.editors.excerpt.on( 'keyup change', debounce( () => {
				rankMathEditor.refresh( 'content' )
			}, 500 ) )
		}

		// WC Events
		this.debounceWCPrice = debounce( this.refreshWCPrice, 500 )
		this.elemPrice.on( 'input', this.debounceWCPrice )
		this.elemRegPrice.on( 'input', this.debounceWCPrice )
	}

	refreshWCPrice() {
		swapVariables.setVariable( 'wc_price', this.getWooCommerceProductPrice() )
		doAction( 'rank_math_update_description_preview' )
	}

	getWooCommerceProductPrice() {
		const price = this.elemPrice.val() ? this.elemPrice.val() : this.elemRegPrice.val()
		return accounting.formatMoney( price, {
			symbol: woocommerce_admin_meta_boxes.currency_format_symbol, // eslint-disable-line
			decimal: woocommerce_admin_meta_boxes.currency_format_decimal_sep, // eslint-disable-line
			thousand: woocommerce_admin_meta_boxes.currency_format_thousand_sep, // eslint-disable-line
			precision: woocommerce_admin_meta_boxes.currency_format_num_decimals, // eslint-disable-line
			format: woocommerce_admin_meta_boxes.currency_format // eslint-disable-line
		} )
	}
}

$( function() {
	new RankMathProductDescription()
} )
