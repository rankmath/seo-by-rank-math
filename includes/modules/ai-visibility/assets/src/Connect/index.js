/**
 * Wordpress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import './style.scss'
import { StatusButton, TextControl } from '@rank-math/components'
import NotRegistered from './NotRegistered'

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

					<NotRegistered activateUrl={ activateUrl } isSiteUrlValid={ isSiteUrlValid } />
				</form>
			</div>
		</div>
	)
}
