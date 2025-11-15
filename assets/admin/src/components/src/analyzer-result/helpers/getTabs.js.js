/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Count the number of category results
 *
 * @param {*} results Category results
 */
export default ( results ) => {
	const { ok, info, warning, fail } = results.statuses
	return [
		{
			name: 'all',
			title: (
				<>
					{ __( 'All', 'rank-math' ) }
					<span className="rank-math-result-filter-count">{ results.total - info }</span>
				</>
			),
			className: 'rank-math-result-filter rank-math-result-filter-all',
			results,
		},

		...( ok > 0
			? [
				{
					name: 'ok',
					title: (
						<>
							{ __( 'Passed Tests', 'rank-math' ) }
							<span className="rank-math-result-filter-count">{ ok }</span>
						</>
					),
					className: 'rank-math-result-filter rank-math-result-filter-passed',
					results,
				},
			]
			: []
		),

		...( warning > 0
			? [
				{
					name: 'warning',
					title: (
						<>
							{ __( 'Warnings', 'rank-math' ) }
							<span className="rank-math-result-filter-count">{ warning }</span>
						</>
					),
					className:
							'rank-math-result-filter rank-math-result-filter-warnings',
					results,
				},
			]
			: []
		),

		...( fail > 0
			? [
				{
					name: 'fail',
					title: (
						<>
							{ __( 'Failed Tests', 'rank-math' ) }
							<span className="rank-math-result-filter-count">{ fail }</span>
						</>
					),
					className: 'rank-math-result-filter rank-math-result-filter-failed',
					results,
				},
			]
			: []
		),
	]
}
