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
import Edit from './components/edit'

export const link = {
	name: 'rankmath/link',
	title: __( 'Link', 'rank-math' ),
	tagName: 'a',
	className: null,
	attributes: {
		url: 'href',
		type: 'data-type',
		id: 'data-id',
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

		// Allows us to ask for this information when we get a report.
		window.console.log( 'Created link:\n\n', pastedText )

		return applyFormat( value, {
			type: 'rankmath/link',
			attributes: {
				url: decodeEntities( pastedText ),
			},
		} )
	},
	edit: Edit,
}
