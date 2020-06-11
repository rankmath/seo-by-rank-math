/**
 * Get overlay images
 *
 * @return {Array} Array of choices.
 */
export function getOverlayChoices() {
	const images = rankMath.overlayImages
	const options = []

	Object.keys( images ).forEach( ( index ) => {
		options.push( {
			label: images[ index ].name,
			value: index,
		} )
	} )

	return options
}
