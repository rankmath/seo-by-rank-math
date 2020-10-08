const getInterval = ( val ) => {
	if ( ! val ) {
		return false
	}

	if ( -1 === val ) {
		const d = new Date()
		d.setYear( 1970 )
		return d.getTime()
	}

	let interval = parseInt( val )
	const unit = val.replace( interval, '' )

	if ( 'd' === unit ) {
		interval = interval * 24 * 60 * 60 * 1000
	}

	if ( 'h' === unit ) {
		interval = interval * 60 * 60 * 1000
	}

	if ( 'm' === unit ) {
		interval = interval * 60 * 1000
	}

	if ( 's' === unit ) {
		interval = interval * 1000
	}

	return Date.now() + interval
}

export function getCookie( key, defaultVal = false ) {
	const cookies = document.cookie ? document.cookie.split( '; ' ) : []
	const rdecode = /(%[0-9A-Z]{2})+/g
	let result = defaultVal

	for ( let i = 0; i < cookies.length; i++ ) {
		const parts = cookies[ i ].split( '=' )
		const name = parts[ 0 ].replace( rdecode, decodeURIComponent )
		let cookie = parts.slice( 1 ).join( '=' )

		if ( key === name ) {
			if ( '"' === cookie.charAt( 0 ) ) {
				cookie = cookie.slice( 1, -1 )
			}

			try {
				cookie = cookie.replace( rdecode, decodeURIComponent )
				try {
					cookie = JSON.parse( cookie )
				} catch ( e ) {}
			} catch ( e ) {}

			result = cookie
			break
		}
	}

	return result
}

export function setCookie( key, value, expires = '30d' ) {
	expires = getInterval( expires )

	const path = '/'
	const date = new Date()
	date.setTime( expires )

	if ( 'object' === typeof value ) {
		value = JSON.stringify( value )
	}

	return ( document.cookie = [
		encodeURIComponent( key ) + '=' + encodeURIComponent( value ),
		expires ? '; expires=' + date.toUTCString() : '', // Use expires attribute, max-age is not supported by IE
		path ? '; path=' + path : '',
	].join( '' ) )
}
