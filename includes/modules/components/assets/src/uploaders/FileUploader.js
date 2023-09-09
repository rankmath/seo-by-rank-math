/**
 * WordPress dependencies
 */
import { RangeControl, FormFileUpload, Button } from '@wordpress/components';

export default ({
	maxFileSize,
	onFileUpload,
	onFileRemove,
	uploadedFileName,
	fileIsUploadingPercentage,
	fileIsUploading,
	fileHasUploaded,
}) => {
	const handleFileUpload = (event) => {
		const file = event.target.files[0];
		if (file) {
			if (file.size > maxFileSize) {
				alert(`File size exceeds ${maxFileSize / 1000000}MB.`);
				return;
			}

			onFileUpload(file);
		}
	};

	return (
		<div className='file-uploader'>
			<div className='uploader__content'>
				{fileIsUploading ? (
					<>
						<RangeControl
							value={fileIsUploadingPercentage}
							min={0}
							max={100}
							withInputField={false}
							showTooltip={false}
						/>
						<span className='uploader__is-uploading'>
							Uploading File: {fileIsUploadingPercentage}%
						</span>
					</>
				) : (
					<>
						{fileHasUploaded && (
							<div className='uploader__uploaded-title'>
								<i className='rm-icon-tick icon'></i>
								<b>File Added: </b>
								<span>{uploadedFileName}</span>
							</div>
						)}
						<div className='uploader__actions'>
							<FormFileUpload
								accept='.xml,.html,application/zip,application/x-rar-compressed'
								onChange={handleFileUpload}
							>
								<i className={fileHasUploaded ? 'rm-icon-trash' : 'rm-icon-export'}></i>
								<span>{fileHasUploaded ? 'Replace File' : 'Add or Upload File'}</span>
							</FormFileUpload>

							{fileHasUploaded && (
								<Button onClick={onFileRemove}>
									<i className='rm-icon-trash'></i> <span>Remove File</span>
								</Button>
							)}
						</div>

						<span>
							File types: xml, hmtl, zip, rar &bull; Max file size: {maxFileSize / 1000000}MB.
						</span>
					</>
				)}
			</div>
		</div>
	);
}