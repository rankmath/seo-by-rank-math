/**
 * External Dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import getResults from './getResults'
import { Button, AnalyzerResult } from '@rank-math/components'
import getLink from '@helpers/getLink'

/**
 * SEO Analyzer admin page contents.
 */
export default () => {
	const results = getResults()
	return (
		<>
			<div className="rank-math-box blurred">
				<h2>{ __( 'Competitor Analyzer', 'rank-math' ) }</h2>

				<p>
					{ __( 'Enter a site URL to see how it ranks for the same SEO criteria as site.', 'rank-math' ) }
				</p>

				<div className="url-form">
					<input
						type="text"
						name="competitor_url"
						id="competitor_url"
						placeholder="https://rankmath.com"
						disabled
					/>

					<Button variant="primary" id="competitor_url_submit" disabled>
						{ __( 'Start SEO Analyzer', 'rank-math' ) }
					</Button>
				</div>
			</div>

			<div className="rank-math-box rank-math-analyzer-result blurred">
				<span className="wp-header-end" />

				{ ! isEmpty( results ) && <AnalyzerResult results={ results } /> }
			</div>

			<div id="rank-math-pro-cta" className="center">
				<div className="rank-math-cta-box blue-ticks width-50 top-20 less-padding">
					<h3>{ __( 'Competitor Analyzer', 'rank-math' ) }</h3>

					<ul>
						<li>
							{ __( 'Analyze competitor websites to gain an edge', 'rank-math' ) }
						</li>
						<li>{ __( 'Evaluate strengths and weaknesses', 'rank-math' ) }</li>
						<li>{ __( 'Explore new keywords and opportunities', 'rank-math' ) }</li>
						<li>
							{ __( 'Make more informed decisions & strategy', 'rank-math' ) }
						</li>
					</ul>

					<Button
						variant="green"
						href={ getLink( 'pro', 'Competitor Analyzer Tab' ) }
						target="_blank"
						rel="noreferrer"
					>
						{ __( 'Upgrade', 'rank-math' ) }
					</Button>
				</div>
			</div>
		</>
	)
}
