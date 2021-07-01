/**
 * WordPress dependencies
 */
import { registerFormatType, unregisterFormatType } from '@wordpress/rich-text'

/**
 * Internal dependencies
 */
import { link } from './link'

wp.domReady( () => {
	[ link ].forEach( ( { name, ...settings } ) => {
		if ( name ) {
			unregisterFormatType( 'core/link' )
			registerFormatType( name, settings )
		}
	} )
} )
