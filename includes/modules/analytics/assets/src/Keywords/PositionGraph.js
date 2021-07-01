/**
 * External dependencies
 */
import { get, isEmpty } from 'lodash'
import ContentLoader from 'react-content-loader'
import {
	BarChart,
	Bar,
	Tooltip as ChartTooltip,
	ResponsiveContainer,
	XAxis,
	YAxis,
	CartesianGrid,
} from 'recharts'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'
import CustomTooltip from '@scShared/CustomTooltip'

const PositionGraph = ( { graph, selected } ) => {
	if ( isEmpty( graph ) ) {
		return (
			<div className="rank-math-graph main-graph keywords-position-graph loader">
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

	const topLabels = {
		top3: __( 'Top 3 Positions', 'rank-math' ),
		top10: __( '4-10 Positions', 'rank-math' ),
		top50: __( '10-50 Positions', 'rank-math' ),
		top100: __( '51-100 Positions', 'rank-math' ),
	}

	return (
		<div className="rank-math-graph main-graph keywords-position-graph">
			<ResponsiveContainer>
				<BarChart
					data={ graph }
					margin={ { top: 0, right: 0, left: 0, bottom: 0 } }
					stackOffset="expand"
				>
					<XAxis
						dy={ 15 }
						dataKey="formattedDate"
						interval="preserveStartEnd"
						minTickGap={ 15 }
						tickLine={ false }
						tickFormatter={ ( value ) => value }
						tick={ { fill: '#7f868d', fontSize: 14 } }
						axisLine={ { stroke: 'rgba(0, 0, 0, 0.15)' } }
					/>
					<YAxis
						dx={ -10 }
						axisLine={ false }
						tickLine={ false }
						tickFormatter={ ( value ) => humanNumber( value ) }
						tick={ { fill: '#7f868d', fontSize: 14 } }
					/>
					<ChartTooltip
						content={ <CustomTooltip /> }
						wrapperStyle={ { zIndex: 10 } }
						wrapperClassName="rank-math-graph-tooltip"
						formatter={ ( value, name ) => [
							value,
							topLabels[ name ],
						] }
						cursor={ { fill: 'rgb(0 0 0 / 0.05)' } }
					/>
					{ selected.top3 && (
						<Bar
							type="basis"
							dataKey="top3"
							strokeWidth={ 0 }
							fill="#3e70b2"
							stackId="keywords"
						/>
					) }

					{ selected.top10 && (
						<Bar
							type="basis"
							dataKey="top10"
							strokeWidth={ 0 }
							fill="#4e8cde"
							stackId="keywords"
						/>
					) }

					{ selected.top50 && (
						<Bar
							type="basis"
							dataKey="top50"
							strokeWidth={ 0 }
							fill="#71a3e5"
							stackId="keywords"
						/>
					) }

					{ selected.top100 && (
						<Bar
							type="basis"
							dataKey="top100"
							strokeWidth={ 0 }
							fill="#83afe8"
							stackId="keywords"
						/>
					) }

					<CartesianGrid
						stroke="rgba(0, 0, 0, 0.05)"
						vertical={ false }
					/>
				</BarChart>
			</ResponsiveContainer>
		</div>
	)
}

export default withSelect( ( select, props ) => {
	const data = select( 'rank-math' ).getKeywordsOverview()
	return {
		...props,
		graph: get( data, [ 'positionGraph' ] ),
	}
} )( PositionGraph )
