/**
 * Internal dependencies
 */
import HelpBox from './help-box'
import ConnectAccount from './connect-account'

export default () => {
	if ( ! rankMath.canUser.manageOptions ) {
		return ''
	}

	return (
		<div className="rank-math-ui container help">
			<ConnectAccount />

			<HelpBox />
		</div>
	)
}
