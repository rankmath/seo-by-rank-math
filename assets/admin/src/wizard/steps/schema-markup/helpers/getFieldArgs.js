/**
 * External Dependencies
 */
import { capitalize, values } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Get field arguments.
 *
 * @param {string} postType    Post type.
 * @param {Array}  schemaTypes Schema Types.
 */
export default ( postType, schemaTypes ) => {
	const fieldId = `pt_${ postType }_default_rich_snippet`

	const fieldName = sprintf(
		/* translators: Post type name */
		__( 'Schema Type for %s', 'rank-math' ),
		capitalize( postType ) + 's'
	)

	if ( postType === 'product' ) {
		return {
			id: fieldId,
			type: 'radio_inline',
			name: fieldName,
			desc: __(
				'Default rich snippet selected when creating a new product.',
				'rank-math'
			),
			options: {
				off: __( 'None', 'rank-math' ),
				product: __( 'Product', 'rank-math' ),
			},
		}
	}

	return {
		id: fieldId,
		type: values( schemaTypes ).length === 2 ? 'radio_inline' : 'select_search',
		name: fieldName,
		desc: __(
			'Default rich snippet selected when creating a new post of this type.',
			'rank-math'
		),
		options: schemaTypes,
		dep: { rich_snippet: true },
	}
}
