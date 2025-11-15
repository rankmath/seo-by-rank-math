
/**
 * External Dependencies
 */
import { filter } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import Help from './help'
import Modules from './modules'
import { getStore } from '../../../../../includes/modules/status/assets/src/store'
import VersionControlApp from '../../../../../includes/modules/status/assets/src/VersionControlApp'

const {
	isAdvancedMode,
	isPluginActiveForNetwork,
	isNetworkAdmin,
	canUser,
} = rankMath

const getTabs = () => {
	if ( canUser.manageOptions && isNetworkAdmin ) {
		getStore()
		return [
			{
				name: 'help',
				title: __( 'Dashboard', 'rank-math' ),
				view: Help,
			},
			{
				name: 'version_control',
				title: __( 'Version Control', 'rank-math' ),
				view: VersionControlApp,
			},
		]
	}

	return filter(
		[
			canUser.manageOptions && {
				name: 'modules',
				title: __( 'Modules', 'rank-math' ),
				view: Modules,
			},
			canUser.manageOptions && ! isPluginActiveForNetwork && {
				name: 'help',
				title: __( 'Help', 'rank-math' ),
				view: Help,
			},
			canUser.manageOptions && {
				name: 'setup-wizard',
				className: 'is-external',
				// Link to external page and prevent the tabPanel from seeing the click.
				title: <a href={ rankMath.adminurl + '?page=rank-math-wizard' } onClick={ ( e ) => ( e.stopPropagation() ) }>
					{ __( 'Setup Wizard', 'rank-math' ) }
				</a>,
			},
			isAdvancedMode && canUser.installPlugins && {
				name: 'import-export',
				className: 'is-external',
				// Link to external page and prevent the tabPanel from seeing the click.
				title: <a href={ rankMath.adminurl + '?page=rank-math-status&view=import_export' } onClick={ ( e ) => ( e.stopPropagation() ) }>
					{ __( 'Import & Export', 'rank-math' ) }
				</a>,
			},
		],
		Boolean
	)
}

export const tabs = getTabs()
