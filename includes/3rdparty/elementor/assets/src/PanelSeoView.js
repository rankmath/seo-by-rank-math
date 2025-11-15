/**
 * External dependencies
 */
import jQuery from 'jquery'
import Marionette from 'Marionette'

/**
 * WordPress dependencies
 */
import { createRoot, createElement } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'

export default Marionette.ItemView.extend( {
	root: null,
	template: false,

	id: 'elementor-panel-rank-math',

	className: 'rank-math-elementor rank-math-sidebar-panel',

	initialize() {
		jQuery( '#elementor-panel-elements-search-area' ).hide()
	},

	onShow() {
		this.root = createRoot( document.getElementById( 'elementor-panel-rank-math' ) )

		/* Filter to include components from the common editor file */
		const element = createElement( applyFilters( 'rank_math_app', {} ) )

		this.root.render( element )
	},

	onDestroy() {
		this.root.unmount()

		jQuery( '#elementor-panel-elements-search-area' ).show()
	},
} )
