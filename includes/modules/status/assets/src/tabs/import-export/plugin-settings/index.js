/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { TabPanel } from '@wordpress/components'

/**
 * Internal Dependencies
 */
import getLink from '@helpers/getLink'
import tabs from './tabs'

/**
 * Import-Export Settings panel template.
 */
export default () => (
	<div className="import-export-settings">
		<h2>{ __( 'Plugin Settings', 'rank-math' ) }</h2>

		<p className="description">
			{ __(
				'Import or export your Rank Math settings. This option is useful for replicating Rank Math settings across multiple websites. ',
				'rank-math'
			) }
			<a
				href={ getLink(
					'import-export-settings',
					'Options Panel Import Export Page'
				) }
				target="_blank"
				rel="noreferrer"
			>
				{ __( 'Learn more about the Import/Export options.', 'rank-math' ) }
			</a>
		</p>

		<div className="rank-math-box no-padding">
			<TabPanel tabs={ tabs }>
				{ ( { view: View } ) => (
					<div className="rank-math-box-content">
						<View />
					</div>
				) }
			</TabPanel>
		</div>
	</div>
)
