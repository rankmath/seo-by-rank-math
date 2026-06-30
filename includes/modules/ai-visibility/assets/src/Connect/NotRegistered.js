/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Button, InvalidSiteUrlNotice } from '@rank-math/components'
import getLink from '@helpers/getLink'

export default ( { activateUrl = '', isSiteUrlValid = true } ) => {
	const ns = 'rank-math-ai-visibility-account-not-registered'

	return (
		<>
			<p>
				{ __(
					'Connect your account to start tracking your brand\'s AI visibility.',
					'seo-by-rank-math'
				) }
				<br />
				<a href={ getLink( 'ai-visibility-connect', 'AI Visibility Connect' ) } target="_blank" rel="noopener noreferrer">
					{ __( 'Need help? View tutorial →', 'seo-by-rank-math' ) }
				</a>
			</p>

			<InvalidSiteUrlNotice isSiteUrlValid={ isSiteUrlValid } />

			<div className="center">
				<Button
					variant="animate"
					href={ activateUrl }
					className="button-connect"
				>
					{ __( 'Connect Now', 'seo-by-rank-math' ) }
				</Button>
			</div>

			<p className={ `${ ns }-note` }>{ __( 'Takes less than 30 seconds to get started', 'seo-by-rank-math' ) }</p>
		</>
	)
}
