/**
 * External dependencies
 */
import { get, times, isEmpty } from 'lodash'
import { useHistory } from 'react-router-dom'
import ContentLoader from 'react-content-loader'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { isPro } from '../functions'
import AnalyticItem from './AnalyticItem'

const AnalyticsOverview = ( { stats } ) => {
	const history = useHistory()

	if ( isEmpty( stats ) ) {
		return (
			<div className="rank-math-box rank-math-score-overview">
				<div className="rank-math-box-grid">
					{ times( 4, ( i ) => (
						<ContentLoader
							key={ i }
							animate={ false }
							backgroundColor="#f0f2f4"
							foregroundColor="#f0f2f4"
							style={ { width: '100%' } }
						>
							<rect
								x="0"
								y="0"
								rx="0"
								ry="0"
								width="100%"
								height="50%"
							/>
						</ContentLoader>
					) ) }
				</div>
			</div>
		)
	}

	const defaultStat = {
		total: 0,
		difference: 0,
	}

	const isAdsenseConnected = isPro() && get( rankMath, 'isAdsenseConnected', false )

	return (
		<div className="rank-math-box rank-math-analytics-overview">
			<div className="rank-math-box-grid">
				<AnalyticItem
					title={ __( 'Search Traffic', 'rank-math' ) }
					tooltip={ __(
						'This is the number of pageviews carried out by visitors from Google.',
						'rank-math'
					) }
					stats={ get( stats, 'pageviews', defaultStat ) }
					graph={ stats.graph.merged }
					dataKey="pageviews"
				/>

				<AnalyticItem
					title={ __( 'Search Impressions', 'rank-math' ) }
					tooltip={ __(
						'This is how many times your site showed up in the search results.',
						'rank-math'
					) }
					stats={ get( stats, 'impressions', defaultStat ) }
					graph={ stats.graph.merged }
					dataKey="impressions"
				/>

				<AnalyticItem
					title={ __( 'Total Keywords', 'rank-math' ) }
					tooltip={ __(
						'This is the total number of keywords your site ranked for.',
						'rank-math'
					) }
					stats={ get(
						stats,
						[ 'keywords', 'keywords' ],
						defaultStat
					) }
					graph={ stats.graph.merged }
					dataKey="keywords"
				/>

				{ ! isAdsenseConnected && (
					<AnalyticItem
						title={ __( 'Search Clicks', 'rank-math' ) }
						tooltip={ __(
							'This is how many times your site was clicked on in the search results.',
							'rank-math'
						) }
						stats={ get( stats, 'clicks', defaultStat ) }
						graph={ stats.graph.merged }
						dataKey="clicks"
					/>
				) }

				{ isAdsenseConnected && (
					<AnalyticItem
						title={ __( 'AdSense', 'rank-math' ) }
						tooltip={ __(
							'This is your total AdSense earning from the time period.',
							'rank-math'
						) }
						stats={ get( stats, 'adsense', defaultStat ) }
						graph={ stats.graph.merged }
						dataKey="earnings"
					/>
				) }
			</div>

			<Button isLink onClick={ () => history.push( '/performance/1' ) }>
				{ __( 'Open Report', 'rank-math' ) }
			</Button>
		</div>
	)
}

export default withSelect( ( select ) => {
	return {
		stats: select( 'rank-math' ).getDashboardStats(
			select( 'rank-math' ).getDaysRange()
		).stats,
	}
} )( AnalyticsOverview )
