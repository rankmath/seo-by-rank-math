/**
 * WordPress dependencies
 */
import { doAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { ResultManager } from '@rankMath/analyzer'
import { getStore } from './redux/store'
import Assessor from './sidebar/Assessor'
import CommonFilters from './commonFilters'

class Editor {
	setup( dataCollector ) {
		getStore()
		this.resultManager = new ResultManager()
		this.assessor = new Assessor( dataCollector )

		new CommonFilters()

		doAction( 'rank_math_loaded' )
	}

	refresh( what ) {
		this.assessor.refresh( what )
	}

	getPrimaryKeyword() {
		return this.assessor.getPrimaryKeyword()
	}

	getSelectedKeyword() {
		return this.assessor.getSelectedKeyword()
	}

	updatePermalink( slug ) {
		throw 'Implement the function'
	}

	updatePermalinkSanitize( slug ) {
		throw 'Implement the function'
	}
}

export default Editor
