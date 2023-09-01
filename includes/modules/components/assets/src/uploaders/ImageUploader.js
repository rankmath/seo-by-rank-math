/**
 * WordPress dependencies
 */
import { RangeControl, FormFileUpload, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default () => {
	const [imageUploadingPercentage, setImageUploadingPercentage] = useState(0);
	const [imageIsUploading, setImageIsUploading] = useState(false);
	const [imageUploaded, setImageUploaded] = useState(false);
	const [uploadedImageSrc, setUploadedImageSrc] = useState(null);

	const handleImageUpload = (event) => {
		const file = event.target.files[0];
		if (file) {
			if (file.size > 500000) {
				alert("File size exceeds 0.5MB. Please choose a smaller image.");
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

				setImageIsUploading(true);

				// Simulates upload progress
				const interval = setInterval(() => {
					setImageUploadingPercentage((prevPercentage) => {
						const newPercentage = prevPercentage + 25;
						if (newPercentage >= 100) {
							clearInterval(interval);
							setImageIsUploading(false);
							setImageUploaded(true);
						}
						return newPercentage;
					});
				}, 500);

				setUploadedImageSrc(URL.createObjectURL(file));
			};

			image.src = URL.createObjectURL(file);
		}
	};

	const handleRemoveImage = () => {
		setImageUploaded(false);
		setUploadedImageSrc(null);
	};

	const uploadingActions = (
		<>
			<RangeControl
				value={imageUploadingPercentage}
				min={0}
				max={100}
				withInputField={false}
				showTooltip={false}
			/>

			<span className='uploader__is-uploading'>Uploading File: {imageUploadingPercentage}%</span>
		</>
	);

	const imageActions = (
		<>
			<div className='uploader__actions'>
				<FormFileUpload accept='image/*' onChange={handleImageUpload}>
					<i className={imageUploaded ? 'rm-icon-trash' : 'rm-icon-export'}></i>
					<span>{imageUploaded ? 'Replace Image' : 'Add or Upload Image'}</span>
				</FormFileUpload>

				{imageUploaded && (
					<Button onClick={handleRemoveImage}>
						<i className='rm-icon-trash'></i> <span>Remove Image</span>
					</Button>
				)}
			</div>

			<span>Min: 1400x1400px. &bull; Max: 3000x3000px. &bull; Max file size: 0.5MB.</span>
		</>
	);

	const imagePreview = (
		<div className='uploader__preview'>
			{imageUploaded ?
				<img src={uploadedImageSrc} alt='' className='uploader__preview-img' />
				:
				<span className='uploader__preview-title'>Image Preview</span>}
		</div>
	);

	return (
		<div className='image-uploader'>
			<div className='uploader__content'>
				{imageIsUploading ? uploadingActions : imageActions}
			</div>

			{imagePreview}
		</div>
	);
}
