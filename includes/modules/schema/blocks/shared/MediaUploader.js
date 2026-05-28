/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import ImagePreview from '@blocks/shared/ImagePreview'

/**
 * Media uploader component.
 *
 * @param {Object}   props                This component's props.
 * @param {number}   props.imageID        The ID of the image to display.
 * @param {string}   props.sizeSlug       The size of the image to display.
 * @param {Function} props.open           Function to open the media library.
 * @param {Function} props.removeImage    Function to remove the image.
 * @param {string}   props.addButtonLabel Label for the add image button.
 * @param {Object}   props.addImageRef    Ref for the add image button.
 */
const MediaUploader = ( {
	imageID,
	sizeSlug,
	open,
	removeImage,
	addButtonLabel = __( 'Add Image', 'seo-by-rank-math' ),
	addImageRef,
} ) => {
	return (
		<div className="rank-math-media-placeholder">
			{ imageID > 0 && (
				<ImagePreview imageID={ imageID } sizeSlug={ sizeSlug } />
			) }
			{ imageID > 0 ? (
				<Button
					ref={ addImageRef }
					icon="edit"
					className="rank-math-replace-image"
					onClick={ open }
				/>
			) : (
				<Button
					ref={ addImageRef }
					onClick={ open }
					className="rank-math-add-image"
					isPrimary
				>
					{ addButtonLabel }
				</Button>
			) }
			{ imageID > 0 && (
				<Button
					icon="no-alt"
					className="rank-math-delete-image"
					onClick={ removeImage }
				/>
			) }
		</div>
	)
}

export default MediaUploader
