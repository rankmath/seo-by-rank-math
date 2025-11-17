/**
 * External Dependencies
 */
import { map, isEmpty } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { TabPanel } from '@wordpress/components'

/**
 * Internal Dependencies
 */
import getLink from '@helpers/getLink'
import ImportPlugin from './ImportPlugin'

export default ( { data, updateViewData } ) => {
	const importablePlugins = data.importablePlugins
	const tabs = map( importablePlugins, ( plugin, slug ) => {
		return {
			slug,
			name: `import-plugin-${ slug }`,
			choices: plugin.choices,
			pluginName: plugin.name,
			title: (
				<>
					<i className="rm-icon rm-icon-import" />
					<span>{ plugin.name }</span>
				</>
			),
		}
	} )

	return (
		<div className="import-plugins">
			<h2>{ __( 'Other Plugins', 'rank-math' ) }</h2>

			<p className="description">
				{ __(
					'If you were using another plugin to add important SEO information to your website before switching to Rank Math SEO, you can import the settings and data here. ',
					'rank-math'
				) }

				<a
					href={ getLink(
						'import-export-settings',
						'Options Panel Import Export Page Other Plugins'
					) }
					target="_blank"
					rel="noreferrer"
				>
					{ __( 'Learn more about the Import/Export options.', 'rank-math' ) }
				</a>
			</p>

			<div
				className="rank-math-box no-padding rank-math-export-form field-form"
			>
				<div className="with-action at-top">
					{ isEmpty( importablePlugins ) ? (
						<p className="empty-notice">
							{ __( 'No plugin detected with importable data.', 'rank-math' ) }
						</p>
					) : (
						<TabPanel tabs={ tabs }>
							{ ( tab ) => (
								<ImportPlugin key={ tab.slug } { ...tab } importablePlugins={ importablePlugins } updateViewData={ updateViewData } />
							) }
						</TabPanel>
					) }
				</div>
			</div>
		</div>
	)
}
