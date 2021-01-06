/**
 * External dependencies
 */
import moment from 'moment'
import { get, map, isEmpty } from 'lodash'
import ContentLoader from 'react-content-loader'
import {
	AreaChart,
	Area,
	XAxis,
	YAxis,
	CartesianGrid,
	Tooltip,
	ResponsiveContainer,
} from 'recharts'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import getColor from '@analytics/colors'
import humanNumber from '@helpers/humanNumber'
import CustomTooltip from '@scShared/CustomTooltip'
import CustomizedAxisTick from '@scShared/CustomizedAxisTick'

const PerformanceGraph = ( { stats, selected } ) => {
	if ( isEmpty( stats ) ) {
		return (
			<div className="rank-math-graph main-graph performance-graph loader">
				<ContentLoader
					animate={ false }
					backgroundColor="#f0f2f4"
					foregroundColor="#f0f2f4"
					style={ { width: '100%', height: '100%' } }
				>
					<rect
						x="0"
						y="0"
						rx="0"
						ry="0"
						width="100%"
						height="100%"
					/>
				</ContentLoader>
			</div>
		)
	}

	let counter = 0
	const graph = stats.graph.merged
	const topLabels = {
		ctr: __( 'Avg. CTR', 'rank-math' ),
		clicks: __( 'Clicks', 'rank-math' ),
		earnings: __( 'Adsense', 'rank-math' ),
		impressions: __( 'Impressions', 'rank-math' ),
		keywords: __( 'Keywords', 'rank-math' ),
		pageviews: __( 'Pageviews', 'rank-math' ),
		position: __( 'Position', 'rank-math' ),
	}
	const selectedCount = Object.values( selected ).filter( Boolean ).length

	return (
		<div className="rank-math-graph main-graph performance-graph">
			<ResponsiveContainer>
				<AreaChart data={ graph }>
					<XAxis
						dy={ 15 }
						dataKey="date"
						interval={ 1 }
						tickLine={ false }
						tickFormatter={ ( value ) =>
							moment( value ).format( 'D MMM, YYYY' )
						}
						tick={ <CustomizedAxisTick /> }
						axisLine={ { stroke: 'rgba(0, 0, 0, 0.15)' } }
						domain={ [ 'dataMin', 'dataMax' ] }
					/>
					<Tooltip
						content={ <CustomTooltip /> }
						wrapperStyle={ { zIndex: 10 } }
						wrapperClassName="rank-math-graph-tooltip"
						formatter={ ( value, name ) => [
							value,
							topLabels[ name ],
						] }
					/>

					<defs>
						{
							map( selected, ( check, id ) => {
								if ( false === check ) {
									return null
								}

								return (
									<linearGradient
										id={ id }
										x1="0"
										y1="0"
										x2="0"
										y2="1"
									>
										<stop
											offset="5%"
											stopColor={ getColor( id ) }
											stopOpacity={ 0.2 }
										/>
										<stop
											offset="95%"
											stopColor={ getColor( id ) }
											stopOpacity={ 0 }
										/>
									</linearGradient>
								)
							} )
						}
					</defs>
					{
						selectedCount < 3 && map( selected, ( check, id ) => {
							if ( false === check ) {
								return null
							}

							++counter

							return (
								<YAxis
									dx={ 1 === counter ? -10 : 10 }
									axisLine={ false }
									tickLine={ false }
									tickFormatter={ ( value ) => humanNumber( value ) }
									tick={ { fill: '#7f868d', fontSize: 14 } }
									yAxisId={ `${ id }-yaxis` }
									orientation={ 1 === counter ? 'left' : 'right' }
								/>
							)
						} )
					}
					{
						map( selected, ( check, id ) => {
							if ( false === check ) {
								return null
							}

							const dataKey = 'adsense' === id ? 'earnings' : id
							return (
								<Area
									connectNulls={ true }
									dataKey={ dataKey }
									stroke={ getColor( id ) }
									strokeWidth={ 2 }
									fill={ `url(#${ id })` }
									yAxisId={ `${ id }-yaxis` }
								/>
							)
						} )
					}
					<CartesianGrid
						stroke="rgba(0, 0, 0, 0.05)"
						vertical={ false }
					/>
				</AreaChart>
			</ResponsiveContainer>
		</div>
	)
}

export default withFilters( 'rankMath.analytics.performanceGraph' )( PerformanceGraph )
