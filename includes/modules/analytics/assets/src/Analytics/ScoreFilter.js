/**
 * External dependencies
 */
import { map, isEmpty } from 'lodash'
import { withRouter } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import getFilterQuery from './getFilterQuery'
import FilterBlock from '@scShared/FilterBlock'
import LoaderFilter from '@scShared/LoaderFilter'
import { isPro } from '../functions'

const scoreRedirect = ( selection, history ) => {
	if ( ! isPro() ) {
		return
	}

	let params = '?'
	map( selection, ( val, key ) => {
		if ( true === val ) {
			params += 'filter=' + key + '&'
		}
	} )

	history.push( '/analytics/1' + params )
}

const ScoreFilter = ( { seoScores, params, history } ) => {
	if ( isEmpty( seoScores ) ) {
		return (
			<LoaderFilter className="rank-math-graph-filter rank-math-score-filters" />
		)
	}

	const selected = getFilterQuery( params )
	const { good, ok, bad, noData } = seoScores

	return (
		<div className="rank-math-graph-filter rank-math-score-filters">
			<FilterBlock
				type="good"
				title={ __( 'Good Score', 'rank-math' ) }
				score={ good }
				tooltipClassName="bottom"
				tooltip={ __(
					'SEO score between 80 and 100. These posts are well optimized and usually do not require further actions.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ ( selection ) => {
					scoreRedirect( selection, history )
				} }
			/>
			<FilterBlock
				type="ok"
				title={ __( 'Fair Score', 'rank-math' ) }
				score={ ok }
				tooltipClassName="bottom"
				tooltip={ __(
					'SEO score between 50 and 80. You may want to revisit these posts for further optimization.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ ( selection ) => {
					scoreRedirect( selection, history )
				} }
			/>
			<FilterBlock
				type="bad"
				title={ __( 'Poor Score', 'rank-math' ) }
				score={ bad }
				tooltipClassName="bottom"
				tooltip={ __(
					'SEO score below 50. These posts are not well optimized and require further optimization.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ ( selection ) => {
					scoreRedirect( selection, history )
				} }
			/>
			<FilterBlock
				type="noData"
				title={ __( 'No Data', 'rank-math' ) }
				score={ noData }
				tooltipClassName="bottom"
				tooltip={ __(
					'These posts have not been analyzed by Rank Math yet.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ ( selection ) => {
					scoreRedirect( selection, history )
				} }
			/>
		</div>
	)
}

export default withRouter(
	withSelect( ( select, props ) => {
		const params = new URLSearchParams( props.location.search )

		return {
			params,
			history: props.history,
			seoScores: select( 'rank-math' ).getAnalyticsSummary().optimization,
		}
	} )( ScoreFilter )
)
