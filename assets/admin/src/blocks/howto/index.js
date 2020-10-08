/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { registerBlockType } from '@wordpress/blocks'
import { addFilter, applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import example from './example'
import edit from './components/edit'
import save from './components/save'
import transforms from './transforms'

/**
 * Register howto block
 */
export default () => {
	const keywords = [
		__( 'HowTo', 'rank-math' ),
		__( 'Schema', 'rank-math' ),
		__( 'SEO', 'rank-math' ),
		__( 'Structured Data', 'rank-math' ),
		__( 'Yoast', 'rank-math' ),
		__( 'Rank Math', 'rank-math' ),
		__( 'Block', 'rank-math' ),
		__( 'Markup', 'rank-math' ),
		__( 'Rich Snippet', 'rank-math' ),
	]

	const attributes = applyFilters( 'rank_math_block_howto_attributes', {
		hasDuration: { type: 'boolean' },
		days: { type: 'string' },
		hours: { type: 'string' },
		minutes: { type: 'string' },
		description: { type: 'string' },
		steps: { type: 'array' },
		sizeSlug: {
			type: 'string',
			default: 'full',
		},
		imageID: { type: 'integer' },
		mainSizeSlug: {
			type: 'string',
			default: 'full',
		},
		titleWrapper: {
			type: 'string',
			default: 'h3',
		},
		textAlign: {
			type: 'string',
			default: '',
		},
		timeLabel: { type: 'string' },
		listStyle: { type: 'string' },
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
	} )

	registerBlockType( 'rank-math/howto-block', {
		title: __( 'HowTo by Rank Math', 'rank-math' ),
		description: __(
			'Easily add Schema-ready, SEO-friendly, HowTo block to your content.',
			'rank-math'
		),
		category: 'rank-math-blocks',
		icon: 'editor-ol',
		supports: { multiple: false },
		keywords,
		attributes,
		example,
		edit,
		save,
		transforms,
	} )
}
