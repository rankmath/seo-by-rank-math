/**
 * External dependencies
 */
import { reject } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import choicesPostTypes from '../../../helpers/choicesPostTypes'
import choicesTaxonomies from '../../../helpers/choicesTaxonomies'
const { llmsUrl } = rankMath

const postTypes = reject( choicesPostTypes, { id: 'attachment' } )
const taxonomies = reject( choicesTaxonomies, { id: 'post_format' } )

export default [
	{
		type: 'notice',
		status: 'info',
		children: (
			<>
				{ __( 'Your llms.txt file is available at: ', 'rank-math' ) }
				<a href={ llmsUrl } target="_blank" rel="noreferrer">
					{ llmsUrl }
				</a>
			</>
		),
	},
	{
		id: 'llms_post_types',
		type: 'checkboxlist',
		name: __( 'Select Post Types', 'rank-math' ),
		desc: __(
			'Select the post types to be included in the llms.txt file.',
			'rank-math'
		),
		options: postTypes,
		toggleAll: true,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'llms_taxonomies',
		type: 'checkboxlist',
		name: __( 'Select Taxonomies', 'rank-math' ),
		desc: __( 'Select the taxonomies to be included in the llms.txt file.', 'rank-math' ),
		options: taxonomies,
		toggleAll: true,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'llms_limit',
		type: 'text',
		name: __( 'Posts/Terms Limit', 'rank-math' ),
		desc: __( 'Maximum number of links to include for each post type.', 'rank-math' ),
		attributes: {
			type: 'number',
			min: 1,
		},
		default: 50,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'llms_extra_content',
		type: 'textarea',
		name: __( 'Additional Content', 'rank-math' ),
		desc: __( 'Add any extra text or links you\'d like to include in your llms.txt file manually.', 'rank-math' ),
		classes: 'rank-math-advanced-option',
	},
]
