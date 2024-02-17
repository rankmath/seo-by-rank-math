/**
 * External dependencies
 */
import { sum, round, isEmpty, isUndefined } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import getClassByScore from '@helpers/getClassByScore'

const ContentAIIcon = ( { isLoaded, score } ) => {
	const isFree = rankMath.contentAIPlan === 'free'
	const classes = classnames( 'rank-math-toolbar-score content-ai-score', {
		[ getClassByScore( score ) ]: true,
		loading: ! isLoaded,
		'is-free': isFree,
	} )

	return (
		<div className={ classes }>
			{ isFree && <span className="rank-math-free-badge">{ __( 'Free', 'rank-math' ) }</span> }
			<i className="rm-icon rm-icon-content-ai"></i>
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
