/**
 * External dependencies
 */
import { get, isEmpty } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import LoaderFilter from '@scShared/LoaderFilter'
import StatFilterBlock from '@scShared/StatFilterBlock'

const StatFilter = ( { stats, selected, setSelection } ) => {
	if ( isEmpty( stats ) ) {
		return (
			<LoaderFilter
				times={ 6 }
				height="73"
				className="rank-math-graph-filter rank-math-stat-filters has-3-col has-6-item"
			/>
		)
	}

	const ctr = get( stats, 'ctr', 0 )
	const clicks = get( stats, 'clicks', 0 )
	const position = get( stats, 'position', 0 )
	const keywords = get( stats, 'keywords', 0 )
	const impressions = get( stats, 'impressions', 0 )

	const classes = classnames( 'rank-math-graph-filter rank-math-stat-filters has-3-col' )

	return (
		<div className={ classes }>
			<StatFilterBlock
				className="stat-filter-color-2"
				type="impressions"
				title={ __( 'Search Impressions', 'rank-math' ) }
				data={ impressions }
				tooltipClassName="bottom"
				tooltip={ __(
					'How many times your site showed up in the search results.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
			<StatFilterBlock
				className="stat-filter-color-4"
				type="keywords"
				title={ __( 'Total Keywords', 'rank-math' ) }
				data={ keywords }
				tooltipClassName="bottom"
				tooltip={ __(
					'Total number of keywords your site ranking below 100 position.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>

			<StatFilterBlock
				className="stat-filter-color-3"
				type="clicks"
				title={ __( 'Search Clicks', 'rank-math' ) }
				data={ clicks }
				tooltipClassName="bottom"
				tooltip={ __(
					'How many times your site was clicked on in the search results.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
			<StatFilterBlock
				className="stat-filter-color-5"
				type="ctr"
				title={ __( 'Avg. CTR', 'rank-math' ) }
				data={ ctr }
				tooltip={ __(
					'Average click-through rate. Search clicks divided by search impressions.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
			<StatFilterBlock
				className="stat-filter-color-6"
				type="position"
				title={ __( 'Avg. Position', 'rank-math' ) }
				data={ position }
				tooltip={ __(
					'Average position of all the ranking keywords below 100 position.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
		</div>
	)
}

export default withFilters( 'rankMath.analytics.performanceStatsFilter' )( StatFilter )
