/**
 * Wordpress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import ModulesList from './ModulesList'
import CTA from './CTA'
import { getStore } from '@rank-math-settings/redux/store'

export default () => {
	const { isPro, canUser } = rankMath

	if ( ! canUser.manageOptions ) {
		return <div>{ __( "You can't access this page.", 'rank-math' ) }</div>
	}

	// Initialize store
	getStore()

	return (
		<div className="rank-math-ui module-listing">
			<div
				className={ `grid ${ isPro ? 'pro-active' : '' }` }
			>
				<CTA />
				<ModulesList />
			</div>
		</div>
	)
}
