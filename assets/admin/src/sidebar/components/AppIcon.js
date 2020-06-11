/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import SVGIcon from '../../../img/menu-icon.svg'
import AnalysisScore from '@components/AnalysisScore'
import getClassByScore from '@helpers/getClassByScore'

const RankMathIcon = ( { isLoaded, score } ) => {
	const classes = classnames( 'rank-math-toolbar-score', {
		[ getClassByScore( score ) ]: true,
		loading: ! isLoaded,
	} )

	return (
		<div className={ classes }>
			<AnalysisScore />
			<SVGIcon />
		</div>
	)
}

export default withSelect( ( select ) => {
	const repo = select( 'rank-math' )

	return {
		isLoaded: repo.isLoaded(),
		score: repo.getAnalysisScore(),
	}
} )( RankMathIcon )
