/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Local SEO Phone numbers fields
 */
export default () => (
	[
		{
			id: 'type',
			type: 'select',
			options: rankMath.phoneTypes,
			default: 'customer support',
		},
		{
			id: 'number',
			type: 'text',
			placeholder: __( 'Format: +1-401-555-1212', 'rank-math-pro' ),
		},
	]
)
