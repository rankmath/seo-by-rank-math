const elemDiv = document.createElement( 'div' )

/**
 * Decodes the HTML entities from a given string.
 *
 * @param {string} text String that contain HTML entities.
 *
 * @return {string} The decoded string.
 */
export default function( text ) {
	if ( text && 'string' === typeof text ) {
		text = text
			.replace( /<script[^>]*>([\S\s]*?)<\/script>/gim, '' )
			.replace( /<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gim, '' )

		elemDiv.innerHTML = text
		text = elemDiv.textContent
		elemDiv.textContent = ''
	}

	return text
}
