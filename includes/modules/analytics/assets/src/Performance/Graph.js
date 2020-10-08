/**
 * External dependencies
 */
import moment from 'moment'
import { isEmpty } from 'lodash'
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

/**
 * Internal dependencies
 */
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

	const graph = stats.graph.merged
	const topLabels = {
		ctr: __( 'CTR', 'rank-math' ),
		clicks: __( 'Clicks', 'rank-math' ),
		impressions: __( 'Impressions', 'rank-math' ),
		keywords: __( 'Keywords', 'rank-math' ),
		pageviews: __( 'Pageviews', 'rank-math' ),
		position: __( 'Position', 'rank-math' ),
	}

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
					<YAxis
						dx={ -10 }
						axisLine={ false }
						tickLine={ false }
						tickFormatter={ ( value ) => humanNumber( value ) }
						tick={ { fill: '#7f868d', fontSize: 14 } }
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
						<linearGradient
							id="pageviews"
							x1="0"
							y1="0"
							x2="0"
							y2="1"
						>
							<stop
								offset="5%"
								stopColor="#10AC84"
								stopOpacity={ 0.3 }
							/>
							<stop
								offset="95%"
								stopColor="#10AC84"
								stopOpacity={ 0 }
							/>
						</linearGradient>
						<linearGradient
							id="impressions"
							x1="0"
							y1="0"
							x2="0"
							y2="1"
						>
							<stop
								offset="5%"
								stopColor="#4e8cde"
								stopOpacity={ 0.2 }
							/>
							<stop
								offset="95%"
								stopColor="#4e8cde"
								stopOpacity={ 0 }
							/>
						</linearGradient>
						<linearGradient id="clicks" x1="0" y1="0" x2="0" y2="1">
							<stop
								offset="5%"
								stopColor="#EE5353"
								stopOpacity={ 0.2 }
							/>
							<stop
								offset="95%"
								stopColor="#EE5353"
								stopOpacity={ 0 }
							/>
						</linearGradient>
						<linearGradient
							id="keywords"
							x1="0"
							y1="0"
							x2="0"
							y2="1"
						>
							<stop
								offset="5%"
								stopColor="#FF9F43"
								stopOpacity={ 0.2 }
							/>
							<stop
								offset="95%"
								stopColor="#FF9F43"
								stopOpacity={ 0 }
							/>
						</linearGradient>
						<linearGradient id="ctr" x1="0" y1="0" x2="0" y2="1">
							<stop
								offset="5%"
								stopColor="#F368E0"
								stopOpacity={ 0.2 }
							/>
							<stop
								offset="95%"
								stopColor="#F368E0"
								stopOpacity={ 0 }
							/>
						</linearGradient>
						<linearGradient
							id="position"
							x1="0"
							y1="0"
							x2="0"
							y2="1"
						>
							<stop
								offset="5%"
								stopColor="#54A0FF"
								stopOpacity={ 0.2 }
							/>
							<stop
								offset="95%"
								stopColor="#54A0FF"
								stopOpacity={ 0 }
							/>
						</linearGradient>
					</defs>
					{ selected.pageviews && (
						<Area
							connectNulls={ true }
							dataKey="pageviews"
							stroke="#10AC84"
							strokeWidth={ 2 }
							fill="url(#pageviews)"
						/>
					) }
					{ selected.impressions && (
						<Area
							connectNulls={ true }
							dataKey="impressions"
							stroke="#4e8cde"
							strokeWidth={ 2 }
							fill="url(#impressions)"
						/>
					) }
					{ selected.clicks && (
						<Area
							connectNulls={ true }
							dataKey="clicks"
							stroke="#EE5353"
							strokeWidth={ 2 }
							fill="url(#clicks)"
						/>
					) }
					{ selected.keywords && (
						<Area
							connectNulls={ true }
							dataKey="keywords"
							stroke="#FF9F43"
							strokeWidth={ 2 }
							fill="url(#keywords)"
						/>
					) }
					{ selected.ctr && (
						<Area
							connectNulls={ true }
							dataKey="ctr"
							stroke="#F368E0"
							strokeWidth={ 2 }
							fill="url(#ctr)"
						/>
					) }
					{ selected.position && (
						<Area
							connectNulls={ true }
							dataKey="position"
							stroke="#54A0FF"
							strokeWidth={ 2 }
							fill="url(#position)"
						/>
					) }
					{ selected.adsense && (
						<Area
							connectNulls={ true }
							dataKey="earnings"
							stroke="#54A0FF"
							strokeWidth={ 2 }
							fill="url(#position)"
						/>
					) }
					<CartesianGrid
						stroke="rgba(0, 0, 0, 0.05)"
						vertical={ false }
					/>
				</AreaChart>
			</ResponsiveContainer>
		</div>
	)
}

export default PerformanceGraph
