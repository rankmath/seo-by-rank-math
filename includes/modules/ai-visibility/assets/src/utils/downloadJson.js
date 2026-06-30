/**
 * Download an object as a pretty-printed JSON file.
 *
 * @since 1.0.273
 *
 * @param {Object} data     Payload to serialise.
 * @param {string} filename Download filename.
 */
const downloadJson = ( data, filename ) => {
	const blob = new Blob( [ JSON.stringify( data, null, 2 ) ], { type: 'application/json' } )
	const url = URL.createObjectURL( blob )
	const a = document.createElement( 'a' )
	a.href = url
	a.download = filename
	a.style.display = 'none'
	document.body.appendChild( a )
	a.click()
	document.body.removeChild( a )
	URL.revokeObjectURL( url )
}

export default downloadJson
