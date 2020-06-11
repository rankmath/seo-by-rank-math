/**
 * Internal dependencies
 */
import { getImageByID } from '@helpers/imageHelper'

/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data'

const ImagePreview = ( { imageUrl } ) => {
	if ( ! imageUrl ) {
		return null
	}

	return <img src={ imageUrl } alt="" />
}

export default withSelect( ( select, props ) => {
	const { imageID, sizeSlug } = props

	return {
		imageUrl: imageID ? getImageByID( imageID, sizeSlug ) : null,
	}
} )( ImagePreview )
