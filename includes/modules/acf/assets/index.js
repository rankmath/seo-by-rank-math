/*global rankMathEditor*/

/**
 * External dependencies
 */
import jQuery from 'jquery'
import { debounce } from 'lodash'

/**
 * Internal dependencies
 */
import { collect } from './src/collector'
import { addFilter } from '@wordpress/hooks'

class App {
	analysisTimeout = 0

	constructor() {
		this.maybeRefresh = this.maybeRefresh.bind( this )
		this.refresh = debounce( this.maybeRefresh, rankMath.acf.refreshRate )
		addFilter( 'rank_math_content', 'rank-math', collect.append.bind( collect ) )
		jQuery( '.acf-field' ).on( 'change', () => {
			this.refresh()
		} )
	}

	maybeRefresh() {
		rankMathEditor.refresh( 'content' )
	}
}
window.RankMathACFAnalysis = new App
