/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import fields from './fields'

export default {
	name: 'blocks',
	header: {
		title: __( 'Blocks', 'rank-math' ),
		description: __(
			'Take control over the default settings available for Rank Math Blocks.',
			'rank-math'
		),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-stories" />
			{ __( 'Blocks', 'rank-math' ) }
		</>
	),
	fields,
}
