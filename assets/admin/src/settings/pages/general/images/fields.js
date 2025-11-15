/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default [
	{
		id: 'add_img_alt',
		type: 'toggle',
		name: __( 'Add missing ALT attributes', 'rank-math' ),
		desc: __(
			'Add <code>alt</code> attributes for <code>images</code> without <code>alt</code> attributes automatically. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.',
			'rank-math'
		),
		default: false,
	},
	{
		id: 'img_alt_format',
		type: 'selectVariable',
		name: __( 'Alt attribute format', 'rank-math' ),
		desc: __(
			'Format used for the new <code>alt</code> attribute values.',
			'rank-math'
		),
		classes: 'large-text rank-math-supports-variables',
		dep: {
			add_img_alt: true,
		},
		exclude: [ 'seo_title', 'seo_description' ],
		default: ' %filename%',
	},
	{
		id: 'add_img_title',
		type: 'toggle',
		name: __( 'Add missing TITLE attributes', 'rank-math' ),
		desc: __(
			'Add <code>TITLE</code> attribute for all <code>images</code> without a <code>TITLE</code> attribute automatically. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.',
			'rank-math'
		),
		default: false,
	},
	{
		id: 'img_title_format',
		type: 'selectVariable',
		name: __( 'Title attribute format', 'rank-math' ),
		desc: __(
			'Format used for the new <code>title</code> attribute values.',
			'rank-math'
		),
		classes: 'large-text rank-math-supports-variables dropdown-up',
		dep: {
			add_img_title: true,
		},
		exclude: [ 'seo_title', 'seo_description' ],
		default: '%title% %count(title)%',
	},
]
