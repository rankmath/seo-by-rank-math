/**
 * WordPress dependencies
 */
import { RangeControl, FormFileUpload, Button } from '@wordpress/components';

export default function ImageUploader({
	maxFileSize,
	onImageUpload,
	onImageRemove,
	uploadedImageSrc,
	imageUploadingPercentage,
	imageIsUploading,
	imageUploaded,
}) {
	const handleImageUpload = (event) => {
		const file = event.target.files[0];
		if (file) {
			if (file.size > maxFileSize) {
				alert(`File size exceeds ${maxFileSize / 1000000}MB. Please choose a smaller image.`);
				return;
			}

			const image = new Image();

			image.onload = () => {
				if (image.width < 1400 || image.height < 1400) {
					alert("Image dimensions are too small. Minimum dimensions required: 1400x1400px.");
					return;
				}
				if (image.width > 3000 || image.height > 3000) {
					alert("Image dimensions are too large. Maximum dimensions allowed: 3000x3000px.");
					return;
				}

				onImageUpload(file);
			};

			image.src = URL.createObjectURL(file);
		}
	};

	return (
		<div className='image-uploader'>
			<div className='uploader__content'>
				{imageIsUploading ? (
					<>
						<RangeControl
							value={imageUploadingPercentage}
							min={0}
							max={100}
							withInputField={false}
							showTooltip={false}
						/>
						<span className='uploader__is-uploading'>
							Uploading File: {imageUploadingPercentage}%
						</span>
					</>
				) : (
					<>
						<div className='uploader__actions'>
							<FormFileUpload accept='image/*' onChange={handleImageUpload}>
								<i className={imageUploaded ? 'rm-icon-trash' : 'rm-icon-export'}></i>
								<span>{imageUploaded ? 'Replace Image' : 'Add or Upload Image'}</span>
							</FormFileUpload>

							{imageUploaded && (
								<Button onClick={onImageRemove}>
									<i className='rm-icon-trash'></i> <span>Remove Image</span>
								</Button>
							)}
						</div>

						<span>Min: 1400x1400px. &bull; Max: 3000x3000px. &bull; Max file size: {maxFileSize / 1000000}MB.</span>
					</>
				)}
			</div>
			<div className='uploader__preview'>
				{imageUploaded ? (
					<img src={uploadedImageSrc} alt='' className='uploader__preview-img' />
				) : (
					<span className='uploader__preview-title'>Image Preview</span>
				)}
			</div>
		</div>
	);
}
