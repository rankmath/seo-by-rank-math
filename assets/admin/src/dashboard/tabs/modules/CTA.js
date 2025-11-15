/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Button } from '@rank-math/components'
import onSiteCheckout from '@helpers/onSiteCheckout'

/**
 * Show CTA if Rank Math Pro is not active.
 */
export default () => {
	if ( rankMath.isPro ) {
		return null
	}

	return (
		<div className="rank-math-box rank-math-unlock-pro-box">
			<i className="rm-icon rm-icon-software"></i>

			<div
				className="pro-link"
				onClick={ () => ( onSiteCheckout() ) }
				aria-hidden="true"
			>
				<header>
					<h3>{ __( 'Take SEO to the Next Level!', 'rank-math' ) }</h3>

					<ul>
						<li>{ __( 'Unlimited personal websites', 'rank-math' ) }</li>
						<li>{ __( 'Free 15 Content AI Credits', 'rank-math' ) }</li>
						<li>{ __( 'Track 500 Keywords', 'rank-math' ) }</li>
						<li>{ __( 'Powerful Schema Generator', 'rank-math' ) }</li>
						<li>{ __( '24/7 Support', 'rank-math' ) }</li>
					</ul>
				</header>

				<div className="status wp-clearfix">
					<Button
						variant="secondary"
						className="button button-sedondary"
					>
						{ __( 'Buy', 'rank-math' ) }
					</Button>
				</div>
			</div>
		</div>
	)
}
