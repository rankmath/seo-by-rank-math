/*global ETBuilderBackendDynamic*/

import jQuery from 'jquery'
import get from 'lodash/get'

const events = [
	// When builder is ready.
	// This event handler receives the builder api as a 2nd argument.
	// https://www.elegantthemes.com/documentation/developers/code-reference/divi-builder-javascript-api/
	'et_builder_api_ready',
	// When changes occure within the builder modules.
	'et_fb_section_content_change',
].join( ' ' )

jQuery( window ).on( events, ( event ) => {
	window.parent.postMessage(
		{
			etBuilderEvent: event.type,
		},
		get( ETBuilderBackendDynamic, 'currentPage.url', '*' )
	)
} )
