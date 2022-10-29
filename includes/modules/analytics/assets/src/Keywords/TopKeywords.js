/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { Dashicon, withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

const TopKeywords = () => {
	return (
		<Fragment>
			<div id="rank-math-pro-cta">
				<div className="rank-math-cta-table">
					<div className="rank-math-cta-header">
						<h2>{ __( 'Top 5 Winning & Losing Keywords', 'rank-mth' ) }</h2>
					</div>
					<div className="rank-math-cta-body">
						<Dashicon size="50" icon="awards" />
						<p>{ __( 'Prioritize what’s most important so you can take action before its too late by seeing keywords you’re ranking well for and keywords where your site’s position has dropped.', 'rank-math' ) }</p>
						<a href={ getLink( 'pro', 'Winning KW CTA' ) } target="_blank" rel="noreferrer" className="button button-primary is-green">{ __( 'Upgrade', 'rank-math' ) }</a>
					</div>
				</div>
			</div>
		</Fragment>
	)
}

export default withFilters( 'rankMath.analytics.topKeywords' )( TopKeywords )
