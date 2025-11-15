/**
 * External Dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import ChangeURLForm from './ChangeURLForm'
import { AnalyzerResult, ProgressBar, Button } from '@rank-math/components'
import { useAnalyzerContext } from './context'

export default () => {
	const { results, startProgress, startAnalysis, analysisError } = useAnalyzerContext()
	const { analyzeSubpage } = rankMath

	if ( ! isEmpty( results ) ) {
		return <AnalyzerResult results={ results } />
	}

	return (
		<>
			{ analysisError && <RawHTML>{ analysisError }</RawHTML> }

			<div className="rank-math-seo-analysis-header">
				{ startProgress ? (
					<>
						{ analyzeSubpage ? (
							<h2>{ __( 'Analysing Page…', 'rank-math' ) }</h2>
						) : (
							<h2>{ __( 'Analysing Website…', 'rank-math' ) }</h2>
						) }

						<ProgressBar />
					</>
				) : (
					<>
						{ analyzeSubpage && <ChangeURLForm /> }

						<Button
							variant="primary"
							size="xlarge"
							className="rank-math-recheck"
							onClick={ startAnalysis }
						>
							{ analyzeSubpage
								? __( 'Start Page Analysis', 'rank-math' )
								: __( 'Start SEO Analyzer', 'rank-math' ) }
						</Button>
					</>
				) }
			</div>
		</>
	)
}
