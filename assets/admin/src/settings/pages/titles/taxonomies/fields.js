/**
 * External dependencies
 */
import { lowerCase, filter, includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import choicesRobots from '../../../helpers/choicesRobots'
import AdvancedRobots from '../../../components/AdvancedRobots'

export default ( taxonomy, labels ) => {
	const prefix = `tax_${ taxonomy }_`
	const name = labels.singular_name

	const fields = [
		{
			id: prefix + 'title',
			type: 'selectVariable',
			name: sprintf(
				/* translators: taxonomy name */
				__( '%s Archive Titles', 'rank-math' ),
				name
			),
			desc: sprintf(
				/* translators: taxonomy name */
				__( 'Title tag for %s archives', 'rank-math' ),
				name
			),
			exclude: [ 'seo_title', 'seo_description' ],
			classes: 'rank-math-supports-variables rank-math-title',
			default: '%term% Archives %page% %sep% %sitename%',
		},
		{
			id: prefix + 'description',
			type: 'selectVariable',
			as: 'textarea',
			name: sprintf(
				/* translators: taxonomy name */
				__( '%s Archive Descriptions', 'rank-math' ),
				name
			),
			desc: sprintf(
				/* translators: taxonomy name */
				__( 'Description for %s archives', 'rank-math' ),
				name
			),
			exclude: [ 'seo_title', 'seo_description' ],
			classes: 'rank-math-supports-variables rank-math-description',
			default: '%term_description%',
		},
		{
			id: prefix + 'custom_robots',
			type: 'toggle',
			name: sprintf(
				/* translators: taxonomy name */
				__( '%s Archives Robots Meta', 'rank-math' ),
				name
			),
			desc: sprintf(
				/* translators: taxonomy name */
				__(
					'Select custom robots meta, such as <code>nofollow</code>, <code>noarchive</code>, etc. for %s archive pages. Otherwise the default meta will be used, as set in the Global Meta tab.',
					'rank-math'
				),
				lowerCase( name )
			),
			classes: 'rank-math-advanced-option',
			default: includes( [ 'post_tag', 'post_format' ], taxonomy ),
		},
		{
			id: prefix + 'robots',
			type: 'checkboxlist',
			name: sprintf(
				/* translators: taxonomy name */
				__( '%s Archives Robots Meta', 'rank-math' ),
				name
			),
			desc: sprintf(
				/* translators: taxonomy name */
				__( 'Custom values for robots meta tag on %s archives.', 'rank-math' ),
				name
			),
			options: choicesRobots,
			dep: {
				[ prefix + 'custom_robots' ]: true,
			},
			classes: 'rank-math-advanced-option rank-math-robots-data',
			default: [ 'index' ],
		},
		{
			id: prefix + 'advanced_robots',
			type: 'component',
			Component: AdvancedRobots,
			name: sprintf(
				/* translators: taxonomy name */
				__( '%s Archives Advanced Robots Meta', 'rank-math' ),
				name
			),
			classes: 'rank-math-advanced-option rank-math-advanced-robots-field',
			dep: {
				[ prefix + 'custom_robots' ]: true,
			},
			default: {
				'max-snippet': -1,
				'max-video-preview': -1,
				'max-image-preview': 'large',
			},
		},
		{
			id: prefix + 'slack_enhanced_sharing',
			type: 'toggle',
			name: __( 'Slack Enhanced Sharing', 'rank-math' ),
			desc: __(
				'When the option is enabled and a term from this taxonomy is shared on Slack, additional information will be shown (the total number of items with this term).',
				'rank-math'
			),
			classes: 'rank-math-advanced-option',
			default: true,
		},
		{
			id: prefix + 'add_meta_box',
			type: 'toggle',
			name: __( 'Add SEO Controls', 'rank-math' ),
			desc: __(
				'Add the SEO Controls for the term editor screen to customize SEO options for individual terms in this taxonomy.',
				'rank-math'
			),
			classes: 'rank-math-advanced-option',
			default: taxonomy === 'category',
		},
		{
			id: prefix + 'bulk_editing',
			type: 'toggleGroup',
			name: __( 'Bulk Editing', 'rank-math' ),
			desc: __(
				'Add bulk editing columns to the terms listing screen.',
				'rank-math'
			),
			options: {
				0: __( 'Disabled', 'rank-math' ),
				editing: __( 'Enabled', 'rank-math' ),
				readonly: __( 'Read Only', 'rank-math' ),
			},
			dep: {
				[ prefix + 'add_meta_box' ]: true,
			},
			classes: 'rank-math-advanced-option',
			default: '0',
		},
		{
			id: 'remove_' + taxonomy + '_snippet_data',
			type: 'toggle',
			name: __( 'Remove Snippet Data', 'rank-math' ),
			desc: sprintf(
				/* translators: taxonomy name */
				__( 'Remove schema data from %s.', 'rank-math' ),
				name
			),
			classes: 'rank-math-advanced-option',
			default: includes( [ 'product_cat', 'product_tag' ], taxonomy ) || taxonomy.substring( 0, 3 ) === 'pa_',
		},
	]

	if ( 'post_format' === taxonomy ) {
		filter(
			fields,
			( field ) =>
				field.id !== prefix + 'add_meta_box' &&
				field.id !== 'remove_' + taxonomy + '_snippet_data'
		)
	}

	return fields
}
