/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

// Add new redirection fields
export default () => {
	return applyFilters(
		'rank_math_redirection_fields',
		[
			{
				id: 'sources',
				type: 'repeatableGroup',
				name: __( 'Source URLs', 'rank-math' ),
				default: { comparison: 'exact' },
				options: {
					addButton: {
						children: __( 'Add another', 'rank-math' ),
					},
					removeButton: {
						children: __( 'Remove', 'rank-math' ),
					},
				},
				classes: 'field-group-text-only',
				fields: [
					{
						id: 'pattern',
						type: 'text',
					},
					{
						id: 'comparison',
						type: 'select',
						options: {
							exact: __( 'Exact', 'rank-math' ),
							contains: __( 'Contains', 'rank-math' ),
							start: __( 'Starts With', 'rank-math' ),
							end: __( 'End With', 'rank-math' ),
							regex: __( 'Regex', 'rank-math' ),
						},
					},
					{
						id: 'ignore',
						type: 'checkbox',
						label: __( 'Ignore Case', 'rank-math' ),
						variant: 'metabox',
					},
				],
			},
			{
				id: 'url_to',
				type: 'text',
				name: __( 'Destination URL', 'rank-math' ),
				dep: {
					header_code: [ '301', '302', '307' ],
				},
			},
			{
				id: 'header_code',
				type: 'toggleGroup',
				name: __( 'Redirection Type', 'rank-math' ),
				options: {
					301: __( '301 Permanent Move', 'rank-math' ),
					302: __( '302 Temporary Move', 'rank-math' ),
					307: __( '307 Temporary Redirect', 'rank-math' ),
					410: __( '410 Content Deleted', 'rank-math' ),
					451: __( '451 Content Unavailable for Legal Reasons', 'rank-math' ),
				},
			},
			{
				id: 'status',
				type: 'toggleGroup',
				name: __( 'Status', 'rank-math' ),
				options: {
					active: __( 'Activate', 'rank-math' ),
					inactive: __( 'Deactivate', 'rank-math' ),
				},
				disableDep: [ [ 'start-date' ], [ 'end-date' ] ],
			},
		]
	)
}
