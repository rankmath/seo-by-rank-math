/**
 * External Dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Interpolate from '@components/Interpolate'

// Get Error message list.
const getMessageList = ( width, isBulkEdit, isFree ) => {
	if ( isBulkEdit ) {
		return (
			<ul>
				<li>{ __( 'Bulk Update Your SEO Meta using AI', 'rank-math' ) }</li>
				<li>{ __( 'Get Access to 40+ AI SEO Tools', 'rank-math' ) }</li>
				<li>{ __( '125+ Expert-Written Prompts', 'rank-math' ) }</li>
				<li>{ __( '1-Click Competitor Content Research', 'rank-math' ) }</li>
				<li>{ __( '1-Click WooCommerce Product Descriptions', 'rank-math' ) }</li>
			</ul>
		)
	}

	if ( isFree ) {
		return (
			<ul>
				<li>{ __( '1-Click Competitor Research', 'rank-math' ) }</li>
				<li>{ __( 'On-Page SEO Suggestions', 'rank-math' ) }</li>
				<li>{ __( '1-Click Bulk SEO Meta', 'rank-math' ) }</li>
				<li>{ __( '125+ Pre-Built Prompts', 'rank-math' ) }</li>
				<li>{ __( 'Multiple RankBot Sessions', 'rank-math' ) }</li>
			</ul>
		)
	}

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

const getProNotice = ( width ) => {
	return (
		<div id="rank-math-pro-cta" className="center rank-math-content-ai-warning-wrapper">
			<div className={ 'rank-math-cta-box blue-ticks top-20 less-padding ' + width }>
				<h3>{ __( '🔒 This is a PRO-Only Feature', 'rank-math' ) }</h3>
				<p>
					{ __( 'We are sorry but this feature is only available to Rank Math PRO/Business/Agency Users. Unlock this feature and many more by getting a Rank Math plan.', 'rank-math' ) }
				</p>
				<ul>
					<li>{ __( 'Bulk Edit SEO Tags', 'rank-math' ) }</li>
					<li>{ __( 'Advanced Google Analytics 4 Integration', 'rank-math' ) }</li>
					<li>{ __( 'Keyword Rank Tracker', 'rank-math' ) }</li>
					<li>{ __( 'Free Content AI Trial', 'rank-math' ) }</li>
					<li>{ __( 'SEO Performance Email Reports', 'rank-math' ) }</li>
				</ul>
				<Button
					href={ rankMath.links.pro }
					target="_blank"
					className="button button-primary is-green"
				>
					{ __( 'Learn More', 'rank-math' ) }
				</Button>
			</div>
		</div>
	)
}

const getCreditsExhaustedMessage = () => {
	const resetDate = rankMath.contentAIRefreshDate ? rankMath.contentAIRefreshDate : ''
	if ( ! resetDate ) {
		return __( 'Your monthly Content AI credits have been fully utilized. To continue enjoying seamless content creation, simply click the button below to upgrade your plan and access more credits.', 'rank-math' )
	}

	return (
		<Interpolate components={ { strong: ( <strong /> ) } }>
			{
				sprintf(
					// Translators: placeholder is the Content AI reset date.
					__(
						'Your monthly Content AI credits have been fully utilized. You can wait till %s for your credits to refresh or upgrade to continue enjoying seamless content creation',
						'rank-math'
					),
					'{{strong}}' + resetDate + '{{/strong}}'
				)
			}
		</Interpolate>
	)
}

// Get Link with UTM parameters.
const getLink = ( link, medium ) => {
	if ( 'free' === rankMath.contentAIPlan ) {
		medium = medium + '+Free+Plan'
	}

	const tags = new URLSearchParams( {
		utm_source: 'Plugin',
		utm_medium: medium,
		utm_campaign: 'WP',
	} )

	if ( ! includes( link, '?' ) ) {
		link = link + '?'
	}

	return link + tags.toString()
}

export default ( { width = 40, showProNotice = false, isBulkEdit = false, isResearch = false, creditsRequired = 0 } ) => {
	if ( showProNotice ) {
		return getProNotice( width )
	}

	const isRegistered = rankMath.isUserRegistered
	const hasContentAIPlan = rankMath.contentAIPlan
	const isFree = hasContentAIPlan === 'free'
	let hasCredits = rankMath.contentAICredits > creditsRequired
	const isMigrating = rankMath.contentAiMigrating

	if ( hasCredits && isResearch && ! isFree && rankMath.contentAICredits < 500 ) {
		hasCredits = false
	}

	if ( isRegistered && hasContentAIPlan && hasCredits && ! isMigrating && ! isFree ) {
		return null
	}

	const widthClass = 'width-' + width
	if ( ! isRegistered || ! hasContentAIPlan || ( hasCredits && isFree ) ) {
		return (
			<div id="rank-math-pro-cta" className="center rank-math-content-ai-warning-wrapper">
				<div className={ 'rank-math-cta-box blue-ticks top-20 less-padding ' + widthClass }>
					<h3>{ __( '🚀 Supercharge Your Content With AI', 'rank-math' ) }</h3>
					<p>
						{ ! isRegistered && ! isBulkEdit && __( 'Start using Content AI by connecting your RankMath.com Account', 'rank-math' ) }
						{ isRegistered && ! hasContentAIPlan && ! isBulkEdit && ! isFree && __( 'To access this Content AI feature, you need to have an active subscription plan.', 'rank-math' ) }
						{ isRegistered && ! isBulkEdit && isFree && __( 'To access this Content AI feature, you have to purchase a Content AI Subscription.', 'rank-math' ) }
						{ isBulkEdit && __( 'You are one step away from unlocking this premium feature along with many more.', 'rank-math' ) }
					</p>
					{ getMessageList( width, isBulkEdit, isFree ) }
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
						isRegistered && ( ! hasContentAIPlan || isFree ) &&
						<Button
							href={ getLink( rankMath.links[ 'content-ai' ] + '?play-video=ioPeVIntJWw&', 'Buy+Plan+Button' ) }
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
						<a href={ rankMath.links.support } target="_blank" rel="noreferrer">{ __( 'reach out to the support team.', 'rank-math' ) }</a>
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
				<p>
					{ getCreditsExhaustedMessage() }
				</p>
				<Button
					href={ getLink( rankMath.links[ 'content-ai' ] + '?play-video=ioPeVIntJWw&', 'Buy+Credits+Button' ) }
					className="button button-primary is-green"
					target="_blank"
				>
					{ __( 'Learn More', 'rank-math' ) }
				</Button>
				<Button
					variant="link"
					href={ getLink( rankMath.links[ 'content-ai-restore-credits' ], 'Buy+Credits+Button' ) }
					className="button button-secondary"
					target="_blank"
				>
					{ __( 'Missing Credits?', 'rank-math' ) }
				</Button>
			</div>
		</div>
	)
}
