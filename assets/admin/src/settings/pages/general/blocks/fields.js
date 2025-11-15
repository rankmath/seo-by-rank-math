/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default [
	{
		id: 'toc_block_title',
		type: 'text',
		name: __( 'Table of Contents Title', 'rank-math' ),
		desc: __(
			'Enter the default title to use for the Table of Contents block.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: __( 'Table of Contents', 'rank-math' ),
	},
	{
		id: 'toc_block_list_style',
		type: 'select',
		name: __( 'Table of Contents List style', 'rank-math' ),
		desc: __(
			'Select the default list style for the Table of Contents block.',
			'rank-math'
		),
		options: {
			div: __( 'None', 'rank-math' ),
			ol: __( 'Numbered', 'rank-math' ),
			ul: __( 'Unordered', 'rank-math' ),
		},
		default: 'ul',
	},
	{
		id: 'toc_block_exclude_headings',
		type: 'checkboxlist',
		name: __( 'Table of Contents Exclude Headings', 'rank-math' ),
		desc: __(
			'Choose the headings to exclude from the Table of Contents block.',
			'rank-math'
		),
		options: [
			{
				id: 'h1',
				label: __( 'Heading H1', 'rank-math' ),
			},
			{
				id: 'h2',
				label: __( 'Heading H2', 'rank-math' ),
			},
			{
				id: 'h3',
				label: __( 'Heading H3', 'rank-math' ),
			},
			{
				id: 'h4',
				label: __( 'Heading H4', 'rank-math' ),
			},
			{
				id: 'h5',
				label: __( 'Heading H5', 'rank-math' ),
			},
			{
				id: 'h6',
				label: __( 'Heading H6', 'rank-math' ),
			},
		],
		toggleAll: true,
	},
]
