/**
 * Return the link.
 *
 * @param {string} id     Id of the link to get.
 * @param {string} medium Medium of the link to get.
 * @return {string}
 */

export default function( id = '', medium = '', hash = '' ) {
	const url = rankMath.links[ id ] || ''
	if ( ! url ) {
		return '#'
	}

	if ( ! medium ) {
		return url
	}

	const params = {
		utm_source: 'Plugin',
		utm_medium: encodeURIComponent( medium ),
		utm_campaign: 'WP',
	}

	// Preserve any existing hash and append UTM params correctly before it.
	let base = url
	let existingHash = ''
	const hashIndex = url.indexOf( '#' )
	if ( hashIndex !== -1 ) {
		base = url.substring( 0, hashIndex )
		existingHash = url.substring( hashIndex )
	}

	const qs = Object.keys( params ).map( ( key ) => `${ key }=${ params[ key ] }` ).join( '&' )
	const joiner = base.includes( '?' ) && ! base.endsWith( '?' ) ? '&' : '?'

	return base + joiner + qs + existingHash + hash
}
