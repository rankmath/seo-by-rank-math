/**
 * External dependencies
 */
import { withRouter } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { Dashicon, withFilters } from '@wordpress/components'

const TopPosts = () => {
	return (
		<Fragment>
			<div id="rank-math-pro-cta">
				<div className="rank-math-cta-table">
					<div className="rank-math-cta-header">
						<h2>{ __( 'Top 5 Winning & Losing Posts', 'rank-mth' ) }</h2>
					</div>
					<div className="rank-math-cta-body">
						<Dashicon size="50" icon="awards" />
						<p>{ __( 'Take full control of what’s important to the success of your website – see content that’s performing well and content that’s dropped in rankings so you can take action.', 'rank-math' ) }</p>
						<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Overview%20Winning%20Posts&utm_campaign=WP" target="_blank" rel="noreferrer" className="button button-primary is-green">{ __( 'Upgrade', 'rank-math' ) }</a>
					</div>
				</div>
			</div>
		</Fragment>
	)
}

export default withRouter( withFilters( 'rankMath.analytics.topPosts' )( TopPosts ) )
