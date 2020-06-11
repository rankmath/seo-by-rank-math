/**
 * External dependencies
 */
import { get } from 'lodash'

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data'

export function getImageBySize( image, sizeSlug ) {
	const url = get( image, [
		'media_details',
		'sizes',
		sizeSlug,
		'source_url',
	] )

	if ( url ) {
		return url
	}

	return get( image, [ 'media_details', 'sizes', 'full', 'source_url' ] )
}

export function getImageByID( imageID, sizeSlug ) {
	const { getMedia } = select( 'core' )
	const image = imageID ? getMedia( imageID ) : null

	if ( null === image ) {
		return null
	}

	return sizeSlug ? getImageBySize( image, sizeSlug ) : image
}
