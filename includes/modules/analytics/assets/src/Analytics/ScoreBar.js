/**
 * External dependencies
 */
import { round, isEmpty } from 'lodash'
import ContentLoader from 'react-content-loader'

/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data'

const getPercentage = ( total, number ) => {
	return round( ( number / total ) * 100, 0 )
}

const ScoreBar = ( { seoScores } ) => {
	if ( isEmpty( seoScores ) ) {
		return (
			<div className="rank-math-score-bar">
				<ContentLoader
					backgroundColor="#dfe4e8"
					foregroundColor="#dfe4e8"
					animate={ false }
					style={ { width: '100%', height: '100%' } }
				>
					<rect
						x="0"
						y="0"
						rx="0"
						ry="0"
						width="100%"
						height="100%"
					/>
				</ContentLoader>
			</div>
		)
	}

	const { total, good, ok, bad } = seoScores

	return (
		<div className="rank-math-score-bar">
			<div
				className="score-bar-good"
				style={ { width: getPercentage( total, good ) + '%' } }
			/>
			<div
				className="score-bar-ok"
				style={ { width: getPercentage( total, ok ) + '%' } }
			/>
			<div
				className="score-bar-bad"
				style={ { width: getPercentage( total, bad ) + '%' } }
			/>
		</div>
	)
}

export default withSelect( ( select ) => {
	return {
		seoScores: select( 'rank-math' ).getAnalyticsSummary().optimization,
	}
} )( ScoreBar )
