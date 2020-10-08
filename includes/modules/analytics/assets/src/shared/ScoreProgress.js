/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * Internal dependencies
 */
import getClassByScore from '@helpers/getClassByScore'

const ScoreProgress = ( { score } ) => {
	score = parseInt( score )
	if ( score < 1 ) {
		return <div className="seo-score no-score">N/A</div>
	}

	const classes = classnames(
		'seo-score',
		getClassByScore( score ),
		{ 'no-fk': 0 === score }
	)

	return (
		<div className={ classes }>
			<span style={ { width: score + '%' } }></span>
			<div className="score-text">{ ( 0 === score ) ? 'N/A' : score }</div>
		</div>
	)
}

export default ScoreProgress
