/**
 * External dependencies
 */
import classnames from 'classnames'
import { map, round, isEmpty } from 'lodash'
import { useHistory } from 'react-router-dom'
import { Facebook } from 'react-content-loader'
import {
	ResponsiveContainer,
	PieChart,
	Pie,
	Sector,
	Cell,
	Tooltip,
	Label,
} from 'recharts'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import { withSelect } from '@wordpress/data'
import { Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { isPro } from '../functions'
import List from '@scShared/woocommerce/List'

const renderActiveShape = ( props ) => {
	const {
		cx,
		cy,
		innerRadius,
		outerRadius,
		startAngle,
		endAngle,
		fill,
	} = props

	return (
		<g>
			<Sector
				cx={ cx }
				cy={ cy }
				innerRadius={ innerRadius }
				outerRadius={ outerRadius }
				startAngle={ startAngle }
				endAngle={ endAngle }
				fill={ fill }
			/>
			<Sector
				cx={ cx }
				cy={ cy }
				startAngle={ startAngle }
				endAngle={ endAngle }
				innerRadius={ outerRadius + 6 }
				outerRadius={ outerRadius + 10 }
				fill={ fill }
			/>
		</g>
	)
}

const RankMathTooltip = ( props ) => {
	const { active } = props
	if ( ! active ) {
		return null
	}

	const { payload } = props

	const classes = classnames(
		'rank-math-graph-tooltip',
		'color-' + payload[ 0 ].name.toLowerCase().replace( / /g, '-' )
	)

	return (
		<div className={ classes }>
			{ `${ payload[ 0 ].name } : ${ payload[ 0 ].value }` }
		</div>
	)
}

const SeoScoreOverview = ( { seoScores } ) => {
	const history = useHistory()
	const [ selected, setSelection ] = useState( 0 )
	if ( isEmpty( seoScores ) ) {
		return (
			<div className="rank-math-box rank-math-score-overview">
				<Facebook
					animate={ false }
					backgroundColor="#f0f2f4"
					foregroundColor="#f0f2f4"
				/>
			</div>
		)
	}

	const seoScoresDetails = [
		{
			title: __( 'Good', 'rank-math' ),
			color: '#10AC84',
			content: seoScores.good,
			className: 'seo-score-good',
			onClick: () => isPro() ? history.push( '/analytics/1?filter=good' ) : null,
		},
		{
			title: __( 'Fair', 'rank-math' ),
			color: '#FF9F43',
			content: seoScores.ok,
			className: 'seo-score-ok',
			onClick: () => isPro() ? history.push( '/analytics/1?filter=ok' ) : null,
		},
		{
			title: __( 'Poor', 'rank-math' ),
			color: '#ed5e5e',
			content: seoScores.bad,
			className: 'seo-score-bad',
			onClick: () => isPro() ? history.push( '/analytics/1?filter=bad' ) : null,
		},
		{
			title: __( 'No Data', 'rank-math' ),
			color: '#dadfe4',
			content: seoScores.noData,
			className: 'seo-score-no-data',
			onClick: () => isPro() ? history.push( '/analytics/1?filter=noData' ) : null,
		},
	]

	const chart = map( seoScoresDetails, ( score ) => {
		return {
			name: score.title,
			value: parseInt( score.content ),
			color: score.color,
		}
	} )

	return (
		<div className="rank-math-box rank-math-score-overview">
			<h3>
				{ __( 'Overall Optimization', 'rank-math' ) }
				<a href="https://rankmath.com/kb/analytics/?utm_source=Plugin&utm_medium=Overall%20Optimization%20Tooltip&utm_campaign=WP" target="_blank" rel="noopener noreferrer" className="rank-math-tooltip">
					<em className="dashicons-before dashicons-editor-help"></em>
				</a>
			</h3>

			<div className="rank-math-box-grid">
				<div className="rank-math-seo-score-graph">
					<ResponsiveContainer aspect={ 1 / 1 }>
						<PieChart>
							<Pie
								activeIndex={ selected }
								activeShape={ renderActiveShape }
								data={ chart }
								innerRadius={ 60 }
								outerRadius={ 80 }
								dataKey="value"
								onMouseEnter={ ( cell, index ) => {
									setSelection( index )
								} }
							>
								{ map( chart, ( entry, index ) => (
									<Cell key={ index } fill={ entry.color } />
								) ) }
								<Label position="center">
									{ round( seoScores.average ) }
								</Label>
							</Pie>
							<Tooltip
								wrapperStyle={ { zIndex: 10 } }
								content={ <RankMathTooltip /> }
								allowEscapeViewBox={ {
									x: true,
									y: true,
								} }
							/>
						</PieChart>
					</ResponsiveContainer>
				</div>

				<List
					className="rank-math-seo-score-list"
					items={ seoScoresDetails }
				/>
			</div>

			<p className="description">
				{ round( seoScores.average ) + ' ' + __(
					'is the average Rank Math’s SEO score. This chart shows how well your posts are optimized based on Rank Math’s scoring system.',
					'rank-math'
				) }
			</p>

			<Button isLink onClick={ () => history.push( '/analytics/1' ) }>
				{ __( 'Open Report', 'rank-math' ) }
			</Button>
		</div>
	)
}

export default withSelect( ( select ) => {
	return {
		seoScores: select( 'rank-math' ).getDashboardStats(
			select( 'rank-math' ).getDaysRange()
		).optimization,
	}
} )( SeoScoreOverview )
