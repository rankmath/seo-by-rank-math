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
 * @param {Object} props This component's props.
 */
const MediaUploader = ( {
	imageID,
	sizeSlug,
	open,
	removeImage,
	addButtonLabel = __( 'Add Image', 'rank-math' ),
} ) => {
	return (
		<div className="rank-math-media-placeholder">
			{ imageID > 0 && (
				<ImagePreview imageID={ imageID } sizeSlug={ sizeSlug } />
			) }
			{ imageID > 0 ? (
				<Button
					icon="edit"
					className="rank-math-replace-image"
					onClick={ open }
				/>
			) : (
				<Button
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
