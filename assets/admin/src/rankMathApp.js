/*eslint no-console:0*/

/**
 * WordPress dependencies
 */
import { addAction } from '@wordpress/hooks'

class RankMathApp {
	constructor() {
		this.methods = []
		this.init = this.init.bind( this )
		this.refresh = this.refresh.bind( this )
		addAction( 'rank_math_loaded', 'rank-math', this.init )
	}

	init() {
		if ( ! this.methods.length ) {
			return
		}

		rankMathEditor.refresh( 'content' )
	}

	registerPlugin() {
		console.warn( 'RankMathApp.registerPlugin is deprecated.' )
	}

	refresh( what ) {
		console.warn(
			'RankMathApp.refresh is deprecated, use rankMathEditor.refresh()'
		)
		this.methods.push( what )
	}

	/**
	 * Function to reload the plugin.
	 *
	 * @param {string} plugin Plugin name.
	 * @param {string} what   Whether to rerun content or title tests.
	 *
	 * @return {void}
	 */
	reloadPlugin( plugin, what = 'content' ) {
		console.warn(
			'RankMathApp.reloadPlugin is deprecated, use rankMathEditor.refresh()'
		)
		this.refresh( what )
	}
}

export default RankMathApp
