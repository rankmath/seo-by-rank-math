/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Table } from '@rank-math/components'
import getConflictingPluginFields from './helpers/getConflictingPluginFields'

export default ( { isWhitelabel, conflictingPlugins } ) => {
	if ( isEmpty( conflictingPlugins ) ) {
		return (
			<p className="conflict-text noconflict">
				{ __( 'No known conflicting plugins found.', 'rank-math' ) }
			</p>
		)
	}

	return (
		<>
			<p className="conflict-text">
				{ isWhitelabel
					? __(
						'The following active plugins on your site may cause conflict issues when used alongside Rank Math: ',
						'rank-math'
					)
					: __(
						'The following active plugins on your site may cause conflict issues when used alongside this plugin: ',
						'rank-math'
					) }
			</p>

			<Table
				className="wizard-conflicts"
				fields={ getConflictingPluginFields( conflictingPlugins ) }
				addHeader={ false }
			/>
		</>
	)
}
