/**
 * External dependencies
 */
import { inRange } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default ( { score } ) => {
	return (
		<div className="rank-math-ca-score">
			<div className="score-text">{ __( 'Score:', 'rank-math' ) } { score }<span> / { __( '100', 'rank-math' ) }</span></div>
			<div className="score-wrapper">
				<span className="score-dot" style={ { left: ( inRange( score, 0, 5 ) ? 5 : score ) + '%' } }></span>
			</div>
		</div>
	)
}
