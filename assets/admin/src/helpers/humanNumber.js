const isFloat = ( number, fixTo = 2 ) => {
	number =
		-1 === number.toString().indexOf( '.' )
			? number
			: number.toFixed( fixTo )

	return number.toString().replace( '.00', '' )
}

/**
 * Convert a number to K, M, B, etc.
 *
 * @param  {number} number Number to make human reable.
 * @return {string} Human reable number.
 */
export default ( number ) => {
	number = parseFloat( number )

	let index = 0
	const threshold = 1e3
	const units = [ 'K', 'M', 'B', 'T', 'Q' ]
	const absNumber = Math.abs( number )

	number = isFloat( absNumber )
	if ( number < 1000 ) {
		return number
	}

	while ( number >= threshold && ++index < units.length ) {
		number /= threshold
	}

	return index === 0 ? number : isFloat( number ) + units[ index - 1 ]
}
