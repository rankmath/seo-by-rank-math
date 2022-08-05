/**
 * External dependencies
 */
import {
	AreaChart,
	Area,
	Tooltip as ChartTooltip,
	ResponsiveContainer,
} from 'recharts'

/**
 * Internal dependencies
 */
import ItemStat from '@scShared/ItemStat'
import Tooltip from '@scShared/Tooltip'
import CustomTooltip from '@scShared/CustomTooltip'

const AnalyticItem = ( { title, tooltip, stats, graph, dataKey } ) => {
	const revertStat = 'position' === dataKey ? true : false
	return (
		<div className="rank-math-analytic-item">
			<h3>
				{ title }
				<Tooltip>{ tooltip }</Tooltip>
			</h3>
			<ItemStat
				{ ...stats }
				revert={ revertStat }
			/>
			<div className="rank-math-graph rank-math-analytic-graph">
				<ResponsiveContainer height={ 50 }>
					<AreaChart
						data={ graph }
						margin={ { top: 0, right: 0, left: 0, bottom: 0 } }
						baseValue="dataMin"
					>
						<ChartTooltip
							content={ <CustomTooltip /> }
							wrapperStyle={ { zIndex: 10, marginTop: 50 } }
							wrapperClassName="rank-math-graph-tooltip"
							formatter={ ( value, name ) => {
								if ( name === 'position' ) {
									return [
										-value,
										name,
									]
								}

								return [
									value,
									name,
								]
							} }
						/>
						<defs>
							<linearGradient
								id="gradient"
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
						</defs>
						<Area
							dataKey={ dataKey }
							stroke="#4e8cde"
							strokeWidth={ 2 }
							fill="url(#gradient)"
						/>
					</AreaChart>
				</ResponsiveContainer>
			</div>
		</div>
	)
}

export default AnalyticItem
