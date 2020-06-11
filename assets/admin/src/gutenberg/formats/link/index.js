/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { isURL } from '@wordpress/url'
import { decodeEntities } from '@wordpress/html-entities'
import { applyFormat, isCollapsed } from '@wordpress/rich-text'

/**
 * Internal dependencies
 */
import edit from './components/edit'

export const link = {
	name: 'rankmath/link',
	title: __( 'Link', 'rank-math' ),
	tagName: 'a',
	className: 'rank-math-link',
	attributes: {
		url: 'href',
		target: 'target',
		rel: 'rel',
	},
	__unstablePasteRule( value, { html, plainText } ) {
		if ( isCollapsed( value ) ) {
			return value
		}

		const pastedText = ( html || plainText )
			.replace( /<[^>]+>/g, '' )
			.trim()

		// A URL was pasted, turn the selection into a link
		if ( ! isURL( pastedText ) ) {
			return value
		}

		return applyFormat( value, {
			type: 'rankmath/link',
			attributes: {
				url: decodeEntities( pastedText ),
			},
		} )
	},
	edit,
}
