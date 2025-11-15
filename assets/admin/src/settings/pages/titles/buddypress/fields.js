/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import choicesRobots from '../../../helpers/choicesRobots'
import AdvancedRobots from '../../../components/AdvancedRobots'

export default [
	{
		id: 'bp_group_title',
		type: 'selectVariable',
		name: __( 'Group Title', 'rank-math' ),
		desc: __( 'Title tag for groups', 'rank-math' ),
		exclude: [ 'seo_title', 'seo_description' ],
		default: '',
	},
	{
		id: 'bp_group_description',
		type: 'selectVariable',
		as: 'textarea',
		name: __( 'Group Description', 'rank-math' ),
		desc: __( 'BuddyPress group description', 'rank-math' ),
		exclude: [ 'seo_title', 'seo_description' ],
	},
	{
		id: 'bp_group_custom_robots',
		type: 'toggle',
		name: __( 'Group Robots Meta', 'rank-math' ),
		desc: __( 'Select custom robots meta for Group archive pages. Otherwise the default meta will be used, as set in the Global Meta tab.', 'rank-math' ),
		classes: 'rank-math-advanced-option',
		default: false,
	},
	{
		id: 'bp_group_robots',
		type: 'checkboxlist',
		name: __( 'Group Robots Meta', 'rank-math' ),
		desc: __( 'Custom values for robots meta tag on groups page.', 'rank-math' ),
		options: choicesRobots,
		classes: 'rank-math-advanced-option rank-math-robots-data',
		dep: {
			bp_group_custom_robots: true,
		},
	},
	{
		id: 'bp_group_advanced_robots',
		type: 'component',
		Component: AdvancedRobots,
		name: __( 'Group Advanced Robots Meta', 'rank-math' ),
		classes: 'rank-math-advanced-option rank-math-advanced-robots-field',
		dep: {
			bp_group_custom_robots: true,
		},
		default: {
			'max-snippet': -1,
			'max-video-preview': -1,
			'max-image-preview': 'large',
		},
	},
]
