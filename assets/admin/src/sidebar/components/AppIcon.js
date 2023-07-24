/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { withSelect, dispatch } from '@wordpress/data'
/**
 * Internal dependencies
 */
import AnalysisScore from '@components/AnalysisScore'
import getClassByScore from '@helpers/getClassByScore'

const RankMathIcon = ( { isLoaded, score, toggleEditor } ) => {
	const classes = classnames( 'rank-math-toolbar-score', {
		[ getClassByScore( score ) ]: true,
		loading: ! isLoaded,
	} )

	return (
		<div className={ classes } onClick={ toggleEditor }>
			<i className="rm-icon rm-icon-rank-math"></i>
			<AnalysisScore />
		</div>
	)
}

export default withSelect( ( select ) => {
	const repo = select( 'rank-math' )

	return {
		isLoaded: repo.isLoaded(),
		score: repo.getAnalysisScore(),
		toggleEditor() {
			dispatch( 'core/editor' ).editPost({meta:{rankmath:'saving_post'}})
		},
	}
} )( RankMathIcon )
