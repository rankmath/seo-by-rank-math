/**
 * External dependencies
 */
import { sum, round, isEmpty, isUndefined } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import getClassByScore from '@helpers/getClassByScore'

const ContentAIIcon = ( { isLoaded, score } ) => {
	const classes = classnames( 'rank-math-toolbar-score content-ai-score', {
		[ getClassByScore( score ) ]: true,
		loading: ! isLoaded,
	} )

	return (
		<div className={ classes }>
			<i className="rm-icon rm-icon-target"></i>
			<span className="content-ai-score">{ score } / 100</span>
		</div>
	)
}

export default withSelect( ( select ) => {
	const repo = select( 'rank-math' )

	let score = Object.values( repo.getContentAIScore() )
	score = isEmpty( score ) || ! rankMath.isUserRegistered ? rankMath.contentAiScore : round( sum( score ) / score.length )

	return {
		isLoaded: repo.isLoaded(),
		score: ! isUndefined( score ) ? score : 0,
	}
} )( ContentAIIcon )
