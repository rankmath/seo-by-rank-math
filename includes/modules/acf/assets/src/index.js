/**
 * External dependencies
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */
import jQuery from 'jquery'
import { debounce } from 'lodash'

/**
 * Internal dependencies
 */
import { collect } from './collector'
import { addFilter } from '@wordpress/hooks'

class App {
	analysisTimeout = 0

	constructor() {
		this.maybeRefresh = this.maybeRefresh.bind( this )
		this.refresh = debounce( this.maybeRefresh, rankMath.acf.refreshRate )
		addFilter(
			'rank_math_content',
			'rank-math',
			collect.append.bind( collect ),
			11
		)

		jQuery( '.acf-field' ).on( 'change', () => {
			this.refresh()
		} )
	}

	maybeRefresh() {
		rankMathEditor.refresh( 'content' )
	}
}
window.RankMathACFAnalysis = new App
