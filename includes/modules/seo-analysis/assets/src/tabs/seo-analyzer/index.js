/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import Result from './Result'
import ResultActions from './ResultActions'

export default () => {
	const { connectUrl, isSiteConnected } = rankMath

	return (
		<>
			<header className="rank-math-box">
				<h2>
					<span className="title-prefix">
						{ __( 'SEO Analysis for', 'rank-math' ) }
					</span>

					<span>{ window.location.hostname }</span>
				</h2>

				{ isSiteConnected && <ResultActions /> }
			</header>

			<div className="rank-math-box rank-math-analyzer-result">
				{ isSiteConnected ? (
					<Result />
				) : (
					<div className="rank-math-seo-analysis-header">
						<h3>
							{ __( 'Analyze your site by ', 'rank-math' ) }

							<a href={ connectUrl } target="_blank" rel="noreferrer">
								{ __( 'linking your Rank Math account', 'rank-math' ) }
							</a>
						</h3>
					</div>
				) }
			</div>
		</>
	)
}
