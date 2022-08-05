/**
 * External dependencies
 */
import jQuery from 'jquery'
import Marionette from 'Marionette'

/**
 * WordPress dependencies
 */
import { createElement, render } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'

export default Marionette.ItemView.extend( {
	template: false,

	id: 'elementor-panel-rank-math',

	className: 'rank-math-elementor rank-math-sidebar-panel',

	initialize() {
		jQuery( '#elementor-panel-elements-search-area' ).hide()
	},

	onShow() {
		render(
			/* Filter to include components from the common editor file */
			createElement( applyFilters( 'rank_math_app', {} ) ),
			document.getElementById( 'elementor-panel-rank-math' )
		)
	},

	onDestroy() {
		jQuery( '#elementor-panel-elements-search-area' ).show()
	},
} )
