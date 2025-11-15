/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import Import from './Import'
import Export from './Export'

export default [
	{
		name: 'rank-math-import-form',
		title: (
			<>
				<i className="rm-icon rm-icon-import" />
				<span className="rank-math-tab-text">
					{ __( 'Import Settings', 'rank-math' ) }
				</span>
			</>
		),
		view: Import,
	},
	{
		name: 'rank-math-export-form',
		title: (
			<>
				<i className="rm-icon rm-icon-export" />
				<span className="rank-math-tab-text">
					{ __( 'Export Settings', 'rank-math' ) }
				</span>
			</>
		),
		view: Export,
	},
]
