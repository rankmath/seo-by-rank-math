/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'

/**
 * Internal dependencies.
 */
import getLink from '@helpers/getLink'

export default ( { width = 80 } ) => {
	const isRegistered = rankMath.isUserRegistered
	const hasContentAIPlan = rankMath.contentAIPlan
	const hasCredits = rankMath.contentAICredits > 0

	if ( isRegistered && hasContentAIPlan && hasCredits ) {
		return null
	}

	const widthClass = 'width-' + width
	if ( ! isRegistered || ! hasContentAIPlan ) {
		return (
			<div id="rank-math-pro-cta" className="center rank-math-content-ai-warning-wrapper">
				<div className={ 'rank-math-cta-box blue-ticks top-20 less-padding ' + widthClass }>
					<h3>{ __( '🚀 Unlock the Power of Content AI', 'rank-math' ) }</h3>
					<p>
						{ ! isRegistered && __( 'Start using Content AI by connecting your RankMath.com Account', 'rank-math' ) }
						{ isRegistered && ! hasContentAIPlan && __( 'Get started with Content AI by purchasing a subscription plan.', 'rank-math' ) }
					</p>
					<ul>
						<ul>
							<li>{ __( 'Gain access to 40+ advanced AI tools, empowering your content strategy.', 'rank-math' ) }</li>
							<li>{ __( 'Experience the revolutionary AI-powered Content Editor for unparalleled efficiency.', 'rank-math' ) }</li>
							<li>{ __( 'Engage with RankBot, your personal AI Chat Assistant, for real-time assistance.', 'rank-math' ) }</li>
						</ul>
					</ul>
					{
						! isRegistered &&
						<Button
							href={ rankMath.connectSiteUrl }
							className="button button-primary is-green"
						>
							{ __( 'Connect Now', 'rank-math' ) }
						</Button>
					}

					{
						isRegistered && ! hasContentAIPlan &&
						<Button
							href={ getLink( 'content-ai-pricing-tables', 'Sidebar No Plan' ) }
							className="button button-primary is-green"
							target="_blank"
						>
							{ __( 'Buy Now', 'rank-math' ) }
						</Button>
					}
				</div>
			</div>
		)
	}

	return (
		<div id="rank-math-pro-cta" className="center rank-math-content-ai-warning-wrapper">
			<div className={ 'rank-math-cta-box less-padding top-20 ' + widthClass }>
				<h3>{ __( '⛔️ Content AI Credit Alert!', 'rank-math' ) }</h3>
				<p>{ __( 'Your monthly Content AI credits have been fully utilized. To continue enjoying seamless content creation, simply click the button below to upgrade your plan and access more credits.', 'rank-math' ) }</p>
				<Button
					href={ getLink( 'content-ai-pricing-tables', 'Content AI Page No Credits' ) }
					className="button button-primary is-green"
					target="_blank"
				>
					{ __( 'Get More Credits', 'rank-math' ) }
				</Button>
			</div>
		</div>
	)
}
