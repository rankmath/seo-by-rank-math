/**
 * External dependencies
 */
import { get, isEmpty } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { isPro } from '../functions'
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
	const adsense = get( stats, 'adsense', 0 )
	const position = get( stats, 'position', 0 )
	const keywords = get( stats, [ 'keywords', 'keywords' ], 0 )
	const pageviews = get( stats, 'pageviews', 0 )
	const impressions = get( stats, 'impressions', 0 )

	const isAdsenseConnected = get( rankMath, 'isAdsenseConnected', false )
	const classes = classnames(
		'rank-math-graph-filter rank-math-stat-filters',
		{
			'has-4-col': isAdsenseConnected,
			'has-3-col': ! isAdsenseConnected,
		}
	)

	return (
		<div className={ classes }>
			<StatFilterBlock
				className="stat-filter-color-1"
				type="pageviews"
				title={ __( 'Search Traffic', 'rank-math' ) }
				data={ pageviews }
				tooltip={ __(
					'This is the number of pageviews carried out by visitors from Google.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
			<StatFilterBlock
				className="stat-filter-color-2"
				type="impressions"
				title={ __( 'Search Impressions', 'rank-math' ) }
				data={ impressions }
				tooltip={ __(
					'This is how many times your site showed up in the search results.',
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
				tooltip={ __(
					'This is the total number of keywords your site ranked for.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>

			{ isPro() && isAdsenseConnected && (
				<StatFilterBlock
					className="stat-filter-color-3"
					type="adsense"
					title={ __( 'AdSense', 'rank-math' ) }
					data={ adsense }
					tooltip={ __(
						'This is your total AdSense earning from the time period.',
						'rank-math'
					) }
					selected={ selected }
					onClick={ setSelection }
				/>
			) }

			<StatFilterBlock
				className="stat-filter-color-3"
				type="clicks"
				title={ __( 'Search Clicks', 'rank-math' ) }
				data={ clicks }
				tooltip={ __(
					'This is how many times your site was clicked on in the search results.',
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
					'This is the average click-through rate (search clicks divided by search impressions).',
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
					'Average click-through rate. Search clicks divided by search impressions.',
					'rank-math'
				) }
				selected={ selected }
				onClick={ setSelection }
			/>
		</div>
	)
}

export default StatFilter
