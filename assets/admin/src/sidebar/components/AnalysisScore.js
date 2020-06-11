/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import getClassByScore from '@helpers/getClassByScore'

const AnalysisScore = ( { score } ) => (
	<div className={ 'seo-score ' + getClassByScore( score ) }>
		<div className="score-text">{ score } / 100</div>
	</div>
)

export default withSelect( ( select ) => {
	const repo = select( 'rank-math' )
	return {
		score: repo.getAnalysisScore(),
		isRefreshing: repo.isRefreshing(),
	}
} )( AnalysisScore )
