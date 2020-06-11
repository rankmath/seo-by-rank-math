/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks'

/**
 * Internal dependencies
 */
import example from './example'
import edit from './components/edit'
import transforms from './transforms'
import save from './components/save'

export default () => {
	const keywords = [
		__( 'FAQ', 'rank-math' ),
		__( 'Frequently Asked Questions', 'rank-math' ),
		__( 'Schema', 'rank-math' ),
		__( 'SEO', 'rank-math' ),
		__( 'Structured Data', 'rank-math' ),
		__( 'Yoast', 'rank-math' ),
		__( 'Rank Math', 'rank-math' ),
		__( 'Block', 'rank-math' ),
		__( 'Markup', 'rank-math' ),
		__( 'Rich Snippet', 'rank-math' ),
	]

	const attributes = {
		listStyle: { type: 'string' },
		sizeSlug: {
			type: 'string',
			default: 'thumbnail',
		},
		titleWrapper: {
			type: 'string',
			default: 'h3',
		},
		questions: {
			type: 'array',
		},
		textAlign: {
			type: 'string',
			default: '',
		},
		listCssClasses: {
			type: 'string',
			default: '',
		},
		titleCssClasses: {
			type: 'string',
			default: '',
		},
		contentCssClasses: {
			type: 'string',
			default: '',
		},
	}

	registerBlockType( 'rank-math/faq-block', {
		title: __( 'FAQ by Rank Math', 'rank-math' ),
		description: __(
			'Easily add Schema-ready, SEO-friendly, Frequently Asked Questions to your content.',
			'rank-math'
		),
		category: 'rank-math-blocks',
		icon: 'editor-ul',
		keywords,
		attributes,
		example,
		edit,
		save,
		transforms,
	} )
}
