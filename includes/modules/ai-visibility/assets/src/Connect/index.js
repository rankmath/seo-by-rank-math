/**
 * Wordpress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import './style.scss'
import { StatusButton, TextControl, ConnectAccountBody } from '@rank-math/components'
import getLink from '@helpers/getLink'

export default ( { config = {} } ) => {
	const { isSiteConnected, registerProductNonce } = config
	const { activateUrl, isSiteUrlValid } = config.aiVisibility ?? {}

	const ns = 'rank-math-ai-visibility-account'

	const status = isSiteConnected ? 'connected' : 'disconnected'
	const statusLabel = isSiteConnected
		? __( 'Connected', 'seo-by-rank-math' )
		: __( 'Not Connected', 'seo-by-rank-math' )

	return (
		<div
			className={ `${ ns } ${
				isSiteConnected ? `${ ns }-connected` : `${ ns }-disconnected`
			}` }
		>
			<header>
				<h3>{ __( 'Account Connection Required', 'seo-by-rank-math' ) }</h3>

				<StatusButton status={ status }>{ statusLabel }</StatusButton>
			</header>

			<div className={ `${ ns }-content` }>
				<form method="post">
					<TextControl
						type="hidden"
						name="registration-action"
						value={ isSiteConnected ? 'deregister' : 'register' }
					/>

					<TextControl
						type="hidden"
						name="_wpnonce"
						value={ registerProductNonce }
					/>

					<ConnectAccountBody
						description={ __(
							'Connect your account for free to start tracking your brand\'s AI visibility.',
							'seo-by-rank-math'
						) }
						helpLink={ getLink( 'ai-visibility-connect', 'AI Visibility Connect' ) }
						activateUrl={ activateUrl }
						isSiteUrlValid={ isSiteUrlValid }
						note={ __( 'Takes less than 30 seconds to get started', 'seo-by-rank-math' ) }
						noteClassName={ `${ ns }-not-registered-note` }
					/>
				</form>
			</div>
		</div>
	)
}
