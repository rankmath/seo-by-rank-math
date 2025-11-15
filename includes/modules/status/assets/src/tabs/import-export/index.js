/**
 * WordPress Dependencies
 */
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal Dependencies
 */
import PluginSettings from './plugin-settings'
import OtherPlugins from './other-plugins'
import SettingsBackup from './settings-backup'

/**
 * Import/Export page template.
 *
 * @param {Object} props Component props.
 */
export default ( props ) => (
	<div className="rank-math-import-export">
		<PluginSettings />
		{ applyFilters( 'rank_math_status_import_export_tabs', '', props ) }
		<OtherPlugins { ...props } />
		<SettingsBackup { ...props } />
	</div>
)
