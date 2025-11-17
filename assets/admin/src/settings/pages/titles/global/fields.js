/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import choicesRobots from '../../../helpers/choicesRobots'
import choicesSeparator from '../../../helpers/choicesSeparator'
import AdvancedRobots from '../../../components/AdvancedRobots'

export default [
	{
		id: 'robots_global',
		type: 'checkboxlist',
		name: __( 'Robots Meta', 'rank-math' ),
		desc: __(
			'Default values for robots meta tag. These can be changed for individual posts, taxonomies, etc.',
			'rank-math'
		),
		classes: 'rank-math-robots-data',
		options: choicesRobots,
		default: [ 'index' ],
	},
	{
		id: 'advanced_robots_global',
		type: 'component',
		Component: AdvancedRobots,
		name: __( 'Advanced Robots Meta', 'rank-math' ),
		classes: 'rank-math-advanced-option rank-math-advanced-robots-field',
		default: {
			'max-snippet': -1,
			'max-video-preview': -1,
			'max-image-preview': 'large',
		},
	},
	{
		id: 'noindex_empty_taxonomies',
		type: 'toggle',
		name: __( 'Noindex Empty Category and Tag Archives', 'rank-math' ),
		desc: __(
			'Setting empty archives to <code>noindex</code> is useful for avoiding indexation of thin content pages and dilution of page rank. As soon as a post is added, the page is updated to <code>index</code>.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: true,
	},
	{
		id: 'title_separator',
		type: 'toggleGroup',
		addCustom: true,
		name: __( 'Separator Character', 'rank-math' ),
		// eslint-disable-next-line @wordpress/i18n-translator-comments
		desc: __(
			'You can use the separator character in titles by inserting <code>%separator%</code> or <code>%sep%</code> in the title fields.',
			'rank-math'
		),
		options: choicesSeparator,
		default: '-',
	},
	...( ! rankMath.supportsTitleTag
		? [
			{
				id: 'rewrite_title',
				type: 'toggle',
				name: __( 'Rewrite Titles', 'rank-math' ),
				desc: __(
					"Your current theme doesn't support title-tag. Enable this option to rewrite page, post, category, search and archive page titles.",
					'rank-math'
				),
				default: false,
			},
		]
		: []
	),
	{
		id: 'capitalize_titles',
		type: 'toggle',
		name: __( 'Capitalize Titles', 'rank-math' ),
		desc: __(
			'Automatically capitalize the first character of each word in the titles.',
			'rank-math'
		),
		default: false,
	},
	{
		id: 'open_graph_image',
		type: 'file',
		name: __( 'OpenGraph Thumbnail', 'rank-math' ),
		desc: __(
			'When a featured image or an OpenGraph Image is not set for individual posts/pages/CPTs, this image will be used as a fallback thumbnail when your post is shared on Facebook. The recommended image size is 1200 x 630 pixels.',
			'rank-math'
		),
	},
	{
		id: 'twitter_card_type',
		type: 'select',
		name: __( 'Twitter Card Type', 'rank-math' ),
		desc: __(
			'Card type selected when creating a new post. This will also be applied for posts without a card type selected.',
			'rank-math'
		),
		options: {
			summary_large_image: __( 'Summary Card with Large Image', 'rank-math' ),
			summary_card: __( 'Summary Card', 'rank-math' ),
		},
		default: 'summary_large_image',
		classes: 'rank-math-advanced-option',
	},
]
