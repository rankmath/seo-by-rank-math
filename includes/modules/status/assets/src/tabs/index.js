/**
 * External Dependencies
 */
import { filter } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { lazy } from '@wordpress/element'

/**
 * Internal Dependencies
 */
const VersionControl = lazy( () => import( /* webpackChunkName: "versionControl" */ './version-control' ) )
const DatabaseTools = lazy( () => import( /* webpackChunkName: "databaseTools" */ './database-tools' ) )
const SystemStatus = lazy( () => import( /* webpackChunkName: "systemStatus" */ './system-status' ) )
const ImportExport = lazy( () => import( /* webpackChunkName: "importExport" */ './import-export' ) )

const {
	isAdvancedMode,
	isPluginActiveForNetwork,
	canUser,
} = rankMath
export default filter( [
	isAdvancedMode && ( ! isPluginActiveForNetwork || canUser.setupNetwork ) && canUser.installPlugins && {
		name: 'version_control',
		title: __( 'Version Control', 'rank-math' ),
		view: VersionControl,
	},

	isAdvancedMode && ( ! isPluginActiveForNetwork || canUser.manageOptions ) && {
		name: 'tools',
		title: __( 'Database Tools', 'rank-math' ),
		view: DatabaseTools,
	},

	canUser.manageOptions && {
		name: 'status',
		title: __( 'System Status', 'rank-math' ),
		view: SystemStatus,
	},

	isAdvancedMode && canUser.manageOptions && {
		name: 'import_export',
		title: __( 'Import & Export', 'rank-math' ),
		view: ImportExport,
	},
], Boolean )
