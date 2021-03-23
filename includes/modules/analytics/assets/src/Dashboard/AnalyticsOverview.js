/**
 * External dependencies
 */
import { get, times, isEmpty } from 'lodash'
import { withRouter } from 'react-router-dom'
import ContentLoader from 'react-content-loader'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { withFilters, Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import AnalyticItem from './AnalyticItem'

const AnalyticsOverview = ( { stats, history } ) => {
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

	return (
		<div className="rank-math-box rank-math-analytics-overview">
			<div className="rank-math-box-grid">
				<AnalyticItem
					title={ __( 'Search Impressions', 'rank-math' ) }
					tooltip={ __(
						'How many times your site showed up in the search results.',
						'rank-math'
					) }
					stats={ get( stats, 'impressions', defaultStat ) }
					graph={ stats.graph.merged }
					dataKey="impressions"
				/>

				<AnalyticItem
					title={ __( 'Total Keywords', 'rank-math' ) }
					tooltip={ __(
						'Total number of keywords your site ranking below 100 position.',
						'rank-math'
					) }
					stats={ get( stats, 'keywords', defaultStat ) }
					graph={ stats.graph.merged }
					dataKey="keywords"
				/>

				<AnalyticItem
					title={ __( 'Search Clicks', 'rank-math' ) }
					tooltip={ __(
						'How many times your site was clicked on in the search results.',
						'rank-math'
					) }
					stats={ get( stats, 'clicks', defaultStat ) }
					graph={ stats.graph.merged }
					dataKey="clicks"
				/>

				<AnalyticItem
					title={ __( 'Avg. CTR', 'rank-math' ) }
					tooltip={ __(
						'Average click-through rate. Search clicks divided by search impressions.',
						'rank-math'
					) }
					stats={ get( stats, 'ctr', defaultStat ) }
					graph={ stats.graph.merged }
					dataKey="ctr"
				/>
			</div>

			<Button isLink onClick={ () => history.push( '/performance/1' ) }>
				{ __( 'Open Report', 'rank-math' ) }
			</Button>
		</div>
	)
}

export default withRouter(
	withFilters( 'rankMath.analytics.dashboardAnalyticsOverview' )(
		withSelect( ( select ) => {
			return {
				stats: select( 'rank-math' ).getDashboardStats(
					select( 'rank-math' ).getDaysRange()
				).stats,
			}
		} )( AnalyticsOverview )
	)
)
