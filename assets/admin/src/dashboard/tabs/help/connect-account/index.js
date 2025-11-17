/**
 * Wordpress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { StatusButton, TextControl } from '@rank-math/components'
import NotRegistered from './NotRegistered'
import Registered from './Registered'

export default () => {
	const { isSiteConnected, registerProductNonce } = rankMath

	const status = isSiteConnected ? 'connected' : 'disconnected'
	const statusLabel = isSiteConnected
		? __( 'Connected', 'rank-math' )
		: __( 'Not Connected', 'rank-math' )

	return (
		<div
			className={ `rank-math-box ${
				isSiteConnected ? 'status-green' : 'status-red'
			}` }
		>
			<header>
				<h3>{ __( 'Account', 'rank-math' ) }</h3>

				<StatusButton status={ status }>{ statusLabel }</StatusButton>
			</header>

			<div className="rank-math-box-content rank-math-ui">
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

					{ isSiteConnected ? <Registered /> : <NotRegistered /> }
				</form>
			</div>
		</div>
	)
}
