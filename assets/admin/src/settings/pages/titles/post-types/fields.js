/**
 * External dependencies
 */
import { filter, includes, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf, _x } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import choicesRobots from '../../../helpers/choicesRobots'
import AdvancedRobots from '../../../components/AdvancedRobots'
import getAdminURL from '../../../helpers/getAdminURL'
import appData from '../../../helpers/appData'

// Dummy variables

const {
	isWebStoriesActive,
	isWooCommerceActive,
	isEddActive,
} = rankMath

const slackEnhancedSharingDescription = ( postType, name ) => {
	if ( 'page' === postType ) {
		return __(
			'When the option is enabled and a page is shared on Slack, additional information will be shown (estimated time to read).',
			'rank-math'
		)
	} else if ( 'product' === postType ) {
		return __(
			'When the option is enabled and a product is shared on Slack, additional information will be shown (price & availability).',
			'rank-math'
		)
	} else if ( 'download' === postType ) {
		return __(
			'When the option is enabled and a product is shared on Slack, additional information will be shown (price).',
			'rank-math'
		)
	}

	return sprintf(
		// Translators: Post type name.
		__(
			'When the option is enabled and a %s is shared on Slack, additional information will be shown (estimated time to read and author).',
			'rank-math'
		),
		name
	)
}

export default ( postType ) => {
	let fields = []
	const prefix = `pt_${ postType }_`
	const postTypeObj = rankMath[ postType ]
	if ( isUndefined( postTypeObj ) ) {
		return fields
	}

	const schemaTypes = postTypeObj.schemaTypes
	const taxonomies = postTypeObj.taxonomies

	if ( 'attachment' === postType && rankMath.isRedirectAttachments ) {
		fields.push( {
			id: 'redirect_attachment_notice',
			type: 'notice',
			status: 'warning',
			children: (
				<p
					dangerouslySetInnerHTML={ {
						__html: sprintf(
							/* translators: The settings page link */
							__(
								'To configure meta tags for your media attachment pages, you need to first %s to parent.',
								'rank-math'
							),
							`<a href="${ getAdminURL( 'options-general' ) }">${ __(
								'disable redirect attachments',
								'rank-math'
							) }</a>`
						),
					} }
				/>
			),
		} )

		return fields
	}

	const name = postTypeObj.name
	const isStoriesPostType = isWebStoriesActive && 'web-story' === postType

	fields.push( {
		id: prefix + 'title',
		type: 'selectVariable',
		name: sprintf(
			/* translators: post type name */
			__( 'Single %s Title', 'rank-math' ),
			name
		),
		desc: sprintf(
			/* translators: post type name */
			__(
				'Default title tag for single %s pages. This can be changed on a per-post basis on the post editor screen.',
				'rank-math'
			),
			name
		),
		classes: 'rank-math-supports-variables rank-math-title',
		default: '%title% %page% %sep% %sitename%',
		exclude: [ 'seo_title', 'seo_description' ],
	} )

	fields.push( {
		id: prefix + 'description',
		type: 'selectVariable',
		as: 'textarea',
		name: sprintf(
			/* translators: post type name */
			__( 'Single %s Description', 'rank-math' ),
			name
		),
		desc: sprintf(
			/* translators: post type name */
			__(
				'Default description for single %s pages. This can be changed on a per-post basis on the post editor screen.',
				'rank-math'
			),
			name
		),
		classes: 'rank-math-supports-variables rank-math-description',
		default: '%excerpt%',
		exclude: [ 'seo_title', 'seo_description' ],
	} )

	fields.push( {
		id: prefix + 'archive_title',
		type: 'selectVariable',
		name: sprintf(
			/* translators: post type name */
			__( '%s Archive Title', 'rank-math' ),
			name
		),
		desc: sprintf(
			/* translators: post type name */
			__( 'Title for %s archive pages.', 'rank-math' ),
			name
		),
		classes: 'rank-math-supports-variables rank-math-title',
		default: '%title% %page% %sep% %sitename%',
		exclude: [ 'seo_title', 'seo_description' ],
	} )

	fields.push( {
		id: prefix + 'archive_description',
		type: 'selectVariable',
		as: 'textarea',
		name: sprintf(
			/* translators: post type name */
			__( '%s Archive Description', 'rank-math' ),
			name
		),
		desc: sprintf(
			/* translators: post type name */
			__( 'Description for %s archive pages.', 'rank-math' ),
			name
		),
		classes: 'rank-math-supports-variables rank-math-description',
		exclude: [ 'seo_title', 'seo_description' ],
	} )

	if (
		( isWooCommerceActive && 'product' === postType ) ||
		( isEddActive && 'download' === postType )
	) {
		fields.push( {
			id: prefix + 'default_rich_snippet',
			type: 'toggleGroup',
			name: __( 'Schema Type', 'rank-math' ),
			desc: __(
				'Default rich snippet selected when creating a new product.',
				'rank-math'
			),
			options: {
				off: __( 'None', 'rank-math' ),
				product:
					'download' === postType
						? __( 'EDD Product', 'rank-math' )
						: __( 'WooCommerce Product', 'rank-math' ),
			},
			default: postTypeObj.schemaDefault,
		} )
	} else {
		let type = ! isStoriesPostType ? 'selectSearch' : 'select'
		const options = {
			off: __( 'None', 'rank-math' ),
			article: __( 'Article', 'rank-math' ),
		}

		if ( 2 === schemaTypes.length ) {
			type = 'toggleGroup'
		}

		fields.push( {
			type,
			id: prefix + 'default_rich_snippet',
			name: __( 'Schema Type', 'rank-math' ),
			desc: sprintf(
				// Translators: %s is "Article" inside a <code> tag.
				__(
					'Default rich snippet selected when creating a new post of this type. If %s is selected, it will be applied for all existing posts with no Schema selected.',
					'rank-math'
				),
				`<code>${ _x(
					'Article',
					'Schema type name in a field description',
					'rank-math'
				) }</code>`
			),
			options: isStoriesPostType ? options : schemaTypes,
			default: postTypeObj.schemaDefault,
		} )

		fields.push( {
			id: prefix + 'default_snippet_name',
			type: 'selectVariable',
			name: __( 'Headline', 'rank-math' ),
			dep: {
				[ prefix + 'default_rich_snippet' ]: [ 'off', '', false, undefined ],
				compare: '!=',
			},
			default: '%seo_title%',
			classes: 'rank-math-supports-variables rank-math-advanced-option',
		} )

		fields.push( {
			id: prefix + 'default_snippet_desc',
			type: 'selectVariable',
			as: 'textarea',
			name: __( 'Description', 'rank-math' ),
			default: '%seo_description%',
			classes: 'rank-math-supports-variables rank-math-advanced-option',
			dep: {
				[ prefix + 'default_rich_snippet' ]: [ 'off', 'book', 'local', '', undefined, false ],
				compare: '!=',
			},
		} )
	}

	fields.push( {
		id: prefix + 'default_article_type',
		type: 'toggleGroup',
		name: __( 'Article Type', 'rank-math' ),
		desc:
			'person' === appData.knowledgegraph_type
				? `<div class="notice notice-warning inline rank-math-notice"><p> ${ sprintf(
					/* translators: Google article snippet doc link */
					__(
						'Google does not allow Person as the Publisher for articles. Organization will be used instead. You can read more about this <a href="%s" target="_blank">here</a>.',
						'rank-math'
					),
					getLink( 'google-article-schema' )
				) }</p></div>`
				: '',
		options: {
			Article: __( 'Article', 'rank-math' ),
			BlogPosting: __( 'Blog Post', 'rank-math' ),
			NewsArticle: __( 'News Article', 'rank-math' ),
		},
		dep: {
			[ prefix + 'default_rich_snippet' ]: 'article',
		},
		default: postTypeObj.articleType,
	} )

	fields.push( {
		id: prefix + 'custom_robots',
		type: 'toggle',
		name: sprintf(
			/* translators: post type name */
			__( '%s Robots Meta', 'rank-math' ),
			name
		),
		desc: sprintf(
			/* translators: post type name */
			__(
				'Select custom robots meta, such as <code>nofollow</code>, <code>noarchive</code>, etc. for single %s pages. Otherwise the default meta will be used, as set in the Global Meta tab.',
				'rank-math'
			),
			name
		),
		classes: 'rank-math-advanced-option',
		default: postTypeObj.customRobots,
	} )

	fields.push( {
		id: prefix + 'robots',
		type: 'checkboxlist',
		name: sprintf(
			/* translators: post type name */
			__( '%s Robots Meta', 'rank-math' ),
			name
		),
		desc: sprintf(
			/* translators: post type name */ __(
				'Custom values for robots meta tag on %s.',
				'rank-math'
			),
			name
		),
		options: choicesRobots,
		dep: {
			[ prefix + 'custom_robots' ]: true,
		},
		classes: 'rank-math-advanced-option rank-math-robots-data',
		default: [ 'index' ],
	} )

	const advancedRobotsId = prefix + 'advanced_robots'
	fields.push( {
		id: advancedRobotsId,
		type: 'component',
		Component: AdvancedRobots,
		name: sprintf(
			/* translators: post type name */
			__( '%s Advanced Robots Meta', 'rank-math' ),
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
	} )

	fields.push( {
		id: prefix + 'link_suggestions',
		type: 'toggle',
		name: __( 'Link Suggestions', 'rank-math' ),
		desc: __(
			'Enable Link Suggestions meta box for this post type, along with the Pillar Content feature.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: postTypeObj.enableLinkSuggestion,
	} )

	fields.push( {
		id: prefix + 'ls_use_fk',
		type: 'toggleGroup',
		name: __( 'Link Suggestion Titles', 'rank-math' ),
		desc: __(
			'Use the Focus Keyword as the default text for the links instead of the post titles.',
			'rank-math'
		),
		options: {
			titles: __( 'Titles', 'rank-math' ),
			focus_keywords: __( 'Focus Keywords', 'rank-math' ),
		},
		dep: {
			[ prefix + 'link_suggestions' ]: true,
		},
		classes: 'rank-math-advanced-option',
		default: 'titles',
	} )

	if ( taxonomies ) {
		fields.push( {
			id: prefix + 'primary_taxonomy',
			type: 'select',
			name: __( 'Primary Taxonomy', 'rank-math' ),
			desc: sprintf(
				/* translators: post type name */
				__(
					'Choose which taxonomy you want to use with the Primary Term feature. This will also be the taxonomy shown in the Breadcrumbs when a single %1$s is being viewed.',
					'rank-math'
				),
				name
			),
			options: taxonomies,
			classes: 'rank-math-advanced-option',
			default: postTypeObj.primaryTaxonomy,
		} )
	}

	fields.push( {
		id: prefix + 'facebook_image',
		type: 'file',
		name: __( 'Thumbnail for Facebook', 'rank-math' ),
		desc: __(
			'Image displayed when your page is shared on Facebook and other social networks. Use images that are at least 1200 x 630 pixels for the best display on high resolution devices.',
			'rank-math'
		),
	} )

	// Enable/Disable Metabox option.
	if ( 'attachment' === postType ) {
		fields.push( {
			id: prefix + 'bulk_editing',
			type: 'toggleGroup',
			name: __( 'Bulk Editing', 'rank-math' ),
			desc: __(
				'Add bulk editing columns to the post listing screen.',
				'rank-math'
			),
			options: {
				0: __( 'Disabled', 'rank-math' ),
				editing: __( 'Enabled', 'rank-math' ),
				readonly: __( 'Read Only', 'rank-math' ),
			},
			classes: 'rank-math-advanced-option',
			default: 'editing',
		} )
	} else {
		fields.push( {
			id: prefix + 'slack_enhanced_sharing',
			type: 'toggle',
			name: __( 'Slack Enhanced Sharing', 'rank-math' ),
			desc: slackEnhancedSharingDescription( postType, name ),
			classes: 'rank-math-advanced-option',
			default: includes( [ 'post', 'page', 'product', 'download' ], postType ),
		} )

		fields.push( {
			id: prefix + 'add_meta_box',
			type: 'toggle',
			name: __( 'Add SEO Controls', 'rank-math' ),
			desc: __(
				'Add SEO controls for the editor screen to customize SEO options for posts in this post type.',
				'rank-math'
			),
			classes: 'rank-math-advanced-option',
			default: true,
		} )

		fields.push( {
			id: prefix + 'bulk_editing',
			type: 'toggleGroup',
			name: __( 'Bulk Editing', 'rank-math' ),
			desc: __(
				'Add bulk editing columns to the post listing screen.',
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
			default: 'editing',
		} )

		fields.push( {
			id: prefix + 'analyze_fields',
			type: 'textarea',
			name: __( 'Custom Fields', 'rank-math' ),
			desc: __(
				'List of custom fields name to include in the Page analysis. Add one per line.',
				'rank-math'
			),
			classes: 'rank-math-advanced-option',
			default: '',
		} )
	}

	// Archive not enabled.
	if ( ! postTypeObj.hasArchive ) {
		fields = filter(
			fields,
			( field ) =>
				field.id !== prefix + 'archive_title' &&
				field.id !== prefix + 'archive_description' &&
				field.id !== prefix + 'facebook_image'
		)
	}

	if ( 'attachment' === postType ) {
		fields = filter(
			fields,
			( field ) =>
				field.id !== prefix + 'link_suggestions' &&
				field.id !== prefix + 'ls_use_fk'
		)
	}

	if ( isStoriesPostType ) {
		fields = filter(
			fields,
			( field ) =>
				field.id !== prefix + 'default_snippet_desc' &&
				field.id !== prefix + 'description' &&
				field.id !== prefix + 'link_suggestions' &&
				field.id !== prefix + 'ls_use_fk' &&
				field.id !== prefix + 'analyze_fields' &&
				field.id !== prefix + 'bulk_editing' &&
				field.id !== prefix + 'add_meta_box'
		)
	}

	return fields
}
