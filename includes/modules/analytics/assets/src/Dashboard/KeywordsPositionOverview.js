/**
 * External dependencies
 */
import { get, times, isEmpty } from 'lodash'
import { useHistory } from 'react-router-dom'
import ContentLoader from 'react-content-loader'
import {
	BarChart,
	Bar,
	Tooltip as ChartTooltip,
	ResponsiveContainer,
	CartesianGrid,
} from 'recharts'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import ItemStat from '@scShared/ItemStat'
import CustomTooltip from '@scShared/CustomTooltip'

const KeywordsPositionStats = ( { title, data } ) => {
	return (
		<div className="rank-math-keyword-block">
			<h4>{ title }</h4>
			<ItemStat { ...data } />
		</div>
	)
}

const KeywordsPositionOverview = ( {
	top3,
	top10,
	top50,
	ctr,
	ctrDifference,
	graph,
} ) => {
	const history = useHistory()

	if ( isEmpty( top3 ) ) {
		return (
			<div className="rank-math-box rank-math-score-overview">
				<div className="rank-math-box-grid has-3-col">
					{ times( 3, ( i ) => (
						<ContentLoader
							key={ i }
							animate={ false }
							backgroundColor="#f0f2f4"
							foregroundColor="#f0f2f4"
							style={ { width: '100%', height: '80px' } }
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
				<div className="rank-math-box-grid has-5-col">
					{ times( 5, ( i ) => (
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
								height="100%"
							/>
						</ContentLoader>
					) ) }
				</div>
			</div>
		)
	}

	const topLabels = {
		top3: __( 'Top 3 Positions', 'rank-math' ),
		top10: __( '4-10 Positions', 'rank-math' ),
		top50: __( '10-50 Positions', 'rank-math' ),
	}

	return (
		<div className="rank-math-box rank-math-position-overview">
			<h3>{ __( 'Keyword Positions', 'rank-math' ) }</h3>

			<div className="grid">
				<div className="rank-math-position position-top3">
					<KeywordsPositionStats
						title={ __( 'Top 3 Positions', 'rank-math' ) }
						data={ top3 }
					/>
				</div>

				<div className="rank-math-position position-top10">
					<KeywordsPositionStats
						title={ __( '4-10 Positions', 'rank-math' ) }
						data={ top10 }
					/>
				</div>

				<div className="rank-math-position position-top50">
					<KeywordsPositionStats
						title={ __( '10-50 Positions', 'rank-math' ) }
						data={ top50 }
					/>
				</div>
			</div>

			<ResponsiveContainer height={ 160 }>
				<BarChart
					data={ graph }
					margin={ { top: 1, right: 1, left: 1, bottom: 1 } }
					stackOffset="expand"
				>
					<CartesianGrid stroke="#f2f2f2" vertical={ false } />
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
					<Bar
						type="basis"
						dataKey="top3"
						strokeWidth={ 0 }
						fill="#3e70b2"
						stackId="keywords"
					/>
					<Bar
						type="basis"
						dataKey="top10"
						strokeWidth={ 0 }
						fill="#4e8cde"
						stackId="keywords"
					/>
					<Bar
						type="basis"
						dataKey="top50"
						strokeWidth={ 0 }
						fill="#71a3e5"
						stackId="keywords"
					/>
				</BarChart>
			</ResponsiveContainer>

			<div className="rank-math-avg-ctr">
				<KeywordsPositionStats
					title={ __( 'Avg. CTR', 'rank-math' ) }
					data={ {
						total: ctr,
						difference: ctrDifference,
					} }
				/>
			</div>

			<Button isLink onClick={ () => history.push( '/keywords/1' ) }>
				{ __( 'Open Report', 'rank-math' ) }
			</Button>
		</div>
	)
}

export default withSelect( ( select ) => {
	const data = select( 'rank-math' ).getKeywordsOverview()
	return {
		...data.topKeywords,
		graph: get( data, [ 'positionGraph' ] ),
	}
} )( KeywordsPositionOverview )
