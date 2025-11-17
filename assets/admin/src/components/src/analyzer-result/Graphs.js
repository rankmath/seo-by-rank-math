/**
 * External Dependencies
 */
import { map, round } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import SerpPreview from './SerpPreview'
import CircleProgress from './CircleProgress'

/**
 * SEO Analyzer graphs.
 *
 * @param {Object} results          The analysis results.
 * @param {Object} results.metrices Result metrices.
 * @param {Object} results.date     Date when analysis was performed
 * @param {Object} results.serpData SERP data.
 */
export default ( { metrices, date, serpData } ) => {
	const { percent, statuses } = metrices
	const total = metrices.total - statuses.info
	const graphOptions = [
		{
			status: 'good',
			value: statuses.ok,
			label: __( 'Passed Tests', 'rank-math' ),
		},
		{
			status: 'average',
			value: statuses.warning,
			label: __( 'Warnings', 'rank-math' ),
		},
		{
			status: 'bad',
			value: statuses.fail,
			label: __( 'Failed Tests', 'rank-math' ),
		},
	]

	return (
		<div className="rank-math-result-graphs rank-math-box">
			{ date && (
				<div className="rank-math-analysis-date">
					<span>
						{ __( 'Last checked: ', 'rank-math' ) }
					</span>
					{ date.date } { __( ' at ', 'rank-math' ) } { date.time }
				</div>
			) }

			<div className="three-col">
				<div className="graphs-main">
					<div id="rank-math-circle-progress">
						<CircleProgress
							max={ 100 }
							size={ 207 }
							value={ Math.abs( percent ) }
							strokeWidth={ 15 }
						/>

						<div className="result-main-score">
							<strong>{ Math.abs( percent ) }/100</strong>
							<div>{ __( 'SEO Score', 'rank-math' ) }</div>
						</div>
					</div>
				</div>

				<div className="graphs-side">
					<ul className="chart">
						{ map( graphOptions, ( { status, value, label } ) => (
							<li key={ status } className={ `chart-bar-${ status }` }>
								<div className="result-score">
									<div>{ label }</div>
									<strong>
										{ Math.abs( value ) }/{ Math.abs( total ) }
									</strong>
								</div>

								<div className="chart-bar">
									<span
										style={ {
											width: Math.abs( round( ( value / total ) * 100 ) ) + '%',
										} }
									/>
								</div>
							</li>
						) ) }
					</ul>
				</div>

				{ serpData && <SerpPreview { ...serpData } /> }
			</div>
		</div>
	)
}
