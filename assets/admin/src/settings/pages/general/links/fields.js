/**
 * External dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import createLink from '../../../helpers/createLink'
import getAdminURL from '../../../helpers/getAdminURL'

// Dependency variable
const noFollowDeps = {
	nofollow_external_links: true,
	nofollow_image_links: true,
}

const redirectionPageUrl = () => {
	return includes( rankMath.modules, 'redirections' )
		? `<a href="${ getAdminURL( 'options-general' ) }&view=redirections" target="new">${ __( 'Redirection Manager', 'rank-math' ) }</a>`
		: `<span class="rank-math-tooltip">
			${ __( 'Redirections Manager', 'rank-math' ) }
			<span>${ __( 'Please enable Redirections module', 'rank-math' ) }</span>
		</span>`
}

export default [
	{
		id: 'strip_category_base',
		type: 'toggle',
		name: __( 'Strip Category Base', 'rank-math' ),
		desc: sprintf(
			// translators: Link to kb article
			__(
				'Remove /category/ from category archive URLs. %s <br>E.g. <code>example.com/category/my-category/</code> becomes <code>example.com/my-category</code>',
				'rank-math'
			),
			createLink(
				'remove-category-base',
				'Options Panel Strip Category',
				__( 'Why do this?', 'rank-math' )
			)
		),
		classes: 'rank-math-advanced-option',
		default: false,
	},
	{
		id: 'attachment_redirect_urls',
		type: 'toggle',
		name: __( 'Redirect Attachments', 'rank-math' ),
		desc: sprintf(
			// translators: Link to kb article
			__(
				'Redirect all attachment page URLs to the post they appear in. For more advanced redirection control, use the built-in %s.',
				'rank-math'
			),
			redirectionPageUrl()
		),
		classes: 'rank-math-advanced-option',
		default: true,
	},
	{
		id: 'attachment_redirect_default',
		type: 'text',
		name: __( 'Redirect Orphan Attachments', 'rank-math' ),
		desc: __(
			'Redirect attachments without a parent post to this URL. Leave empty for no redirection.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		dep: {
			attachment_redirect_urls: true,
		},
	},
	{
		id: 'nofollow_external_links',
		type: 'toggle',
		name: __( 'Nofollow External Links', 'rank-math' ),
		desc: __(
			'Automatically add <code>rel="nofollow"</code> attribute for external links appearing in your posts, pages, and other post types. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: false,
	},
	{
		id: 'nofollow_image_links',
		type: 'toggle',
		name: __( 'Nofollow Image File Links', 'rank-math' ),
		desc: __(
			'Automatically add <code>rel="nofollow"</code> attribute for links pointing to external image files. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: false,
	},
	{
		id: 'nofollow_domains',
		type: 'textarea',
		name: __( 'Nofollow Domains', 'rank-math' ),
		desc: __(
			'Only add <code>nofollow</code> attribute for the link if target domain is in this list. Add one per line. Leave empty to apply nofollow for <strong>ALL</strong> external domains.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		dep: noFollowDeps,
	},
	{
		id: 'nofollow_exclude_domains',
		type: 'textarea',
		name: __( 'Nofollow Exclude Domains', 'rank-math' ),
		desc: __(
			'The <code>nofollow</code> attribute <strong>will not be added</strong> for the link if target domain is in this list. Add one per line.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		dep: noFollowDeps,
	},
	{
		id: 'new_window_external_links',
		type: 'toggle',
		name: __( 'Open External Links in New Tab/Window', 'rank-math' ),
		desc: __(
			'Automatically add <code>target="_blank"</code> attribute for external links appearing in your posts, pages, and other post types to make them open in a new browser tab or window. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.',
			'rank-math'
		),
		default: true,
	},
]
