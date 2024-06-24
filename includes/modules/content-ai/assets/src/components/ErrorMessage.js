/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'

// Get Error message list.
const getMessageList = ( width ) => {
	if ( width === 40 ) {
		return (
			<ul>
				<li>{ __( '1-Click SEO Content', 'rank-math' ) }</li>
				<li>{ __( '1-Click SEO Meta', 'rank-math' ) }</li>
				<li>{ __( '40+ Specialized AI Tools', 'rank-math' ) }</li>
				<li>{ __( '1-Click Competitor Research', 'rank-math' ) }</li>
				<li>{ __( '125+ Pre-Built Prompts', 'rank-math' ) }</li>
			</ul>
		)
	}

	return (
		<ul>
			<li>{ __( 'Gain access to 40+ advanced AI tools, empowering your content strategy.', 'rank-math' ) }</li>
			<li>{ __( 'Experience the revolutionary AI-powered Content Editor for unparalleled efficiency.', 'rank-math' ) }</li>
			<li>{ __( 'Engage with RankBot, your personal AI Chat Assistant, for real-time assistance.', 'rank-math' ) }</li>
		</ul>
	)
}

export default ( { width = 40 } ) => {
	const isRegistered = rankMath.contentAI.isUserRegistered
	const hasContentAIPlan = rankMath.contentAI.plan
	const hasCredits = rankMath.contentAI.credits > 0
	const isMigrating = rankMath.contentAI.isMigrating

	if ( isRegistered && hasContentAIPlan && hasCredits && ! isMigrating ) {
		return null
	}

	const widthClass = 'width-' + width
	if ( ! isRegistered || ! hasContentAIPlan ) {
		return (
			<div id="rank-math-pro-cta" className="center rank-math-content-ai-warning-wrapper">
				<div className={ 'rank-math-cta-box blue-ticks top-20 less-padding ' + widthClass }>
					<h3>{ __( '🚀 Supercharge Your Content With AI', 'rank-math' ) }</h3>
					<p>
						{ ! isRegistered && __( 'Start using Content AI by connecting your RankMath.com Account', 'rank-math' ) }
						{ isRegistered && ! hasContentAIPlan && __( 'To access this Content AI feature, you need to have an active subscription plan.', 'rank-math' ) }
					</p>
					{ getMessageList( width ) }
					{
						! isRegistered &&
						<Button
							href={ rankMath.contentAI.connectSiteUrl }
							className="button button-primary is-green"
						>
							{ __( 'Connect Now', 'rank-math' ) }
						</Button>
					}

					{
						isRegistered && ! hasContentAIPlan &&
						<Button
							href="https://rankmath.com/kb/how-to-use-content-ai/?play-video=ioPeVIntJWw&utm_source=Plugin&utm_medium=Buy+Plan+Button&utm_campaign=WP"
							className="button button-primary is-green"
							target="_blank"
						>
							{ __( 'Learn More', 'rank-math' ) }
						</Button>
					}
				</div>
			</div>
		)
	}

	if ( isMigrating ) {
		return (
			<div id="rank-math-pro-cta" className="center rank-math-content-ai-warning-wrapper">
				<div style={ { textAlign: 'center' } } className={ 'rank-math-cta-box less-padding top-20 ' + widthClass }>
					<h3>{ __( 'Server Maintenance Underway', 'rank-math' ) }</h3>
					<p>
						{
							__( 'We are working on improving your Content AI experience. Please wait for 5 minutes and then refresh to start using the optimized Content AI. If you see this for more than 5 minutes, please ', 'rank-math' )
						}
						<a href="https://rankmath.com/support/" target="_blank" rel="noreferrer">{ __( 'reach out to the support team.', 'rank-math' ) }</a>
						{ __( ' We are sorry for the inconvenience.', 'rank-math' ) }
					</p>
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
					href="https://rankmath.com/kb/how-to-use-content-ai/?play-video=ioPeVIntJWw&utm_source=Plugin&utm_medium=Buy+Credits+Button&utm_campaign=WP"
					className="button button-primary is-green"
					target="_blank"
				>
					{ __( 'Learn More', 'rank-math' ) }
				</Button>
			</div>
		</div>
	)
}
