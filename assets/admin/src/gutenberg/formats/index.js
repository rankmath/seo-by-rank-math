/**
 * WordPress dependencies
 */
import { registerFormatType } from '@wordpress/rich-text'

/**
 * Internal dependencies
 */
import { link } from './link'

wp.domReady( () => {
	[ link ].forEach( ( { name, ...settings } ) => {
		if ( name ) {
			registerFormatType( name, settings )
		}
	} )
} )
