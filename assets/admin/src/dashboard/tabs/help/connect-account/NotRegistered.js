/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Button, InvalidSiteUrlNotice } from '@rank-math/components'

export default () => {
	const { activateUrl, isSiteUrlValid } = rankMath

	return (
		<>
			<p>
				{ __(
					'The plugin is currently not connected with your Rank Math account. Click on the button below to login or register for FREE using your ',
					'rank-math'
				) }

				<strong>{ __( 'Google account, Facebook account', 'rank-math' ) }</strong>

				{ __( ' or ', 'rank-math' ) }

				<strong>{ __( 'your email account.', 'rank-math' ) }</strong>
			</p>

			<InvalidSiteUrlNotice isSiteUrlValid={ isSiteUrlValid } />

			<div className="center">
				<Button
					variant="animate"
					href={ activateUrl }
					className="button-connect"
					disabled={ ! isSiteUrlValid }
				>
					{ __( 'Connect Now', 'rank-math' ) }
				</Button>
			</div>
		</>
	)
}
