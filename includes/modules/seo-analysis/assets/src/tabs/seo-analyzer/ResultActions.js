/**
 * External Dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { Dashicon } from '@wordpress/components'

/**
 * Internal Dependencies
 */
import getLink from '@helpers/getLink'
import { Button } from '@rank-math/components'
import { useAnalyzerContext } from './context'

export default () => {
	const { results, updateResults, startAnalysis } = useAnalyzerContext()

	if ( isEmpty( results ) ) {
		return
	}

	return (
		<div className="analyzer-result-actions">
			<Button
				variant="link"
				className="rank-math-recheck"
				onClick={ () => {
					updateResults( [] )
					startAnalysis()
				} }
			>
				{ __( 'Restart SEO Analyzer', 'rank-math' ) }
				<Dashicon icon="update" />
			</Button>

			<div className="analyzer-results-header">
				{
					applyFilters(
						'rank_math_seo_analysis_print_result',
						<div id="print-results">
							<Button
								href={ getLink( 'pro', 'SEO Analyzer Print Button' ) }
								variant="secondary"
								className="rank-math-print-results is-inactive"
								target="_blank"
							>
								<span className="dashicons dashicons-printer"></span>
								{ __( 'Print', 'rank-math' ) }
								<span className="rank-math-pro-badge">PRO</span>
							</Button>
						</div>
					)
				}

				<Button
					variant="primary"
					href="#analysis-result"
					className="rank-math-view-issues"
				>
					{ __( 'View Issues', 'rank-math' ) }
				</Button>
			</div>
		</div>
	)
}
