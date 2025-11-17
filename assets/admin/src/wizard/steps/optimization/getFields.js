/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

export default () => {
	return [
		{
			id: 'noindex_empty_taxonomies',
			type: 'toggle',
			name: __( 'Noindex Empty Category and Tag Archives', 'rank-math' ),
			desc: __(
				'Setting empty archives to <code>noindex</code> is useful for avoiding indexation of thin content pages and dilution of page rank. As soon as a post is added, the page is updated to <code>index</code>.',
				'rank-math'
			),
			classes: 'rank-math-advanced-option',
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
		},
		{
			id: 'new_window_external_links',
			type: 'toggle',
			name: __( 'Open External Links in New Tab/Window', 'rank-math' ),
			desc: __(
				'Automatically add a <code>target="_blank"</code> attribute to external links appearing in your posts, pages, and other post types. The attributes are applied when the content is displayed, which does not change the stored content.',
				'rank-math'
			),
		},
	]
}
