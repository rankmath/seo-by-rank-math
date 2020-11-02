/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import LoaderFilter from '@scShared/LoaderFilter'
import StatFilterBlock from '@scShared/StatFilterBlock'

const StatFilter = ( {
	top3,
	top10,
	top50,
	top100,
	selected,
	setSelection,
} ) => {
	if ( isEmpty( top3 ) ) {
		return (
			<LoaderFilter
				height="73"
				className="rank-math-graph-filter rank-math-stat-filters has-4-item"
			/>
		)
	}

	return (
		<div className="rank-math-graph-filter rank-math-stat-filters has-4-col">
			<StatFilterBlock
				type="top3"
				title={ __( 'Top 3 Positions', 'rank-math' ) }
				data={ top3 }
				tooltipClassName="bottom"
				tooltip={ __(
					'Your site appears in the best position for these keywords.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
			<StatFilterBlock
				type="top10"
				title={ __( '4-10 Positions', 'rank-math' ) }
				data={ top10 }
				tooltipClassName="bottom"
				tooltip={ __(
					'Your site appears on the first page for these keywords, but not in the top 3 positions.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
			<StatFilterBlock
				type="top50"
				title={ __( '10-50 Positions', 'rank-math' ) }
				data={ top50 }
				tooltipClassName="bottom"
				tooltip={ __(
					'Your site appears somewhere on pages 2-5 of the search results for these keywords.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
			<StatFilterBlock
				type="top100"
				title={ __( '51-100 Positions', 'rank-math' ) }
				data={ top100 }
				tooltipClassName="bottom"
				tooltip={ __(
					'Your site appears in the search results for these keywords, but not on the first couple of pages.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
		</div>
	)
}

export default withSelect( ( select ) => {
	return select( 'rank-math' ).getKeywordsOverview().topKeywords
} )( StatFilter )
