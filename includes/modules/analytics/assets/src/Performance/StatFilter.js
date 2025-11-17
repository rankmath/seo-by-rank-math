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
				className="rank-math-graph-filter rank-math-stat-filters has-4-col has-6-item"
			/>
		)
	}

	const ctr = get( stats, 'ctr', 0 )
	const clicks = get( stats, 'clicks', 0 )
	const position = get( stats, 'position', 0 )
	if ( position && position.previous !== 0 ) {
		position.revert = true
	}
	const keywords = get( stats, 'keywords', 0 )
	const impressions = get( stats, 'impressions', 0 )

	const classes = classnames( 'rank-math-graph-filter rank-math-stat-filters has-4-col has-6-item' )

	return (
		<div className={ classes }>
			<StatFilterBlock
				className="stat-filter-color-2"
				type="impressions"
				title={ __( 'Total Impressions', 'rank-math' ) }
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
				className="stat-filter-color-3"
				type="keywords"
				title={ __( 'Total Keywords', 'rank-math' ) }
				data={ keywords }
				tooltipClassName="bottom"
				tooltip={ __(
					'Total number of keywords your site ranks for within top 100 positions.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>

			<StatFilterBlock
				className="stat-filter-color-4"
				type="clicks"
				title={ __( 'Total Clicks', 'rank-math' ) }
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
				title={ __( 'CTR', 'rank-math' ) }
				data={ ctr }
				tooltip={ __(
					'Average click-through rate. Total clicks divided by total impressions.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
			<StatFilterBlock
				className="stat-filter-color-6"
				type="position"
				title={ __( 'Average Position', 'rank-math' ) }
				data={ position }
				tooltip={ __(
					'Average position of all the keywords ranking within top 100 positions.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
		</div>
	)
}

export default withFilters( 'rankMath.analytics.performanceStatsFilter' )( StatFilter )
