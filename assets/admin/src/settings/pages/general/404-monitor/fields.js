/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default [
	{
		id: '404_advanced_monitor',
		type: 'notice',
		status: 'error',
		children: __(
			'If you have hundreds of 404 errors, your error log might increase quickly. Only choose this option if you have a very few 404s and are unable to replicate the 404 error on a particular URL from your end.',
			'rank-math'
		),
		dep: {
			'404_monitor_mode': 'advanced',
		},
	},
	{
		id: '404_monitor_mode',
		type: 'toggleGroup',
		name: __( 'Mode', 'rank-math' ),
		desc: __(
			'The Simple mode only logs URI and access time, while the Advanced mode creates detailed logs including additional information such as the Referer URL.',
			'rank-math'
		),
		options: {
			simple: __( 'Simple', 'rank-math' ),
			advanced: __( 'Advanced', 'rank-math' ),
		},
		default: 'simple',
	},
	{
		id: '404_monitor_limit',
		type: 'text',
		name: __( 'Log Limit', 'rank-math' ),
		desc: __(
			'Sets the max number of rows in a log. Set to 0 to disable the limit.',
			'rank-math'
		),
		attributes: { type: 'number' },
		default: '100',
	},
	{
		id: '404_monitor_exclude',
		type: 'repeatableGroup',
		exclude: true,
		name: __( 'Exclude Paths', 'rank-math' ),
		desc: __(
			'Enter URIs or keywords you wish to prevent from getting logged by the 404 monitor.',
			'rank-math'
		),
		options: {
			addButton: {
				children: __( 'Add another', 'rank-math' ),
			},
			removeButton: {
				children: __( 'Remove', 'rank-math' ),
			},
		},
		default: [],
		fields: [
			{
				id: 'exclude',
				type: 'text',
			},
			{
				id: 'comparison',
				type: 'select',
				options: rankMath.choicesComparisonTypes,
			},
		],
	},
	{
		id: '404_monitor_ignore_query_parameters',
		type: 'toggle',
		name: __( 'Ignore Query Parameters', 'rank-math' ),
		desc: __(
			'Turn ON to ignore all query parameters (the part after a question mark in a URL) when logging 404 errors.',
			'rank-math'
		),
		default: false,
	},
]
