/**
 * Internal dependencies
 */
import '../../scss/file-uploader.scss'

/**
 * WordPress dependencies
 */
import { RangeControl, FormFileUpload, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function () {
  const [fileIsUploadingPercentage, setFileIsUploadingPercentage] = useState(0);
  const [fileIsUploading, setFileIsUploading] = useState(false);
  const [fileHasUploaded, setFileHasUploaded] = useState(false);
  const [uploadedFileName, setUploadedFileName] = useState('');

  const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
      if (file.size > 500000) {
        alert("File size exceeds 0.5MB.");
        return;
      }

      setFileIsUploading(true);

      // Simulates upload progress
      const interval = setInterval(() => {
        setFileIsUploadingPercentage((prevPercentage) => {
          const newPercentage = prevPercentage + 25;
          if (newPercentage >= 100) {
            clearInterval(interval);
            setFileIsUploading(false);
            setFileHasUploaded(true);
            setUploadedFileName(file.name);
          }
          return newPercentage;
        });
      }, 500);
    }
  };

  const handleRemoveImage = () => {
    setFileHasUploaded(false);
    setUploadedFileName(null);
  };

  const uploadingActions = (
    <>
      <RangeControl
        value={fileIsUploadingPercentage}
        min={0}
        max={100}
        withInputField={false}
        showTooltip={false}
      />

      <span className='file-uploader__is-uploading'>
        Uploading File: {fileIsUploadingPercentage}%
      </span>
    </>
  );

  const fileActions = (
    <>
      {fileHasUploaded && (
        <div className='file-uploader__uploaded-title'>
          <i className='rm-icon-tick icon'></i>
          <b>File Added: </b>
          <span>{uploadedFileName}</span>
        </div>
      )}

      <div className='file-uploader__actions'>
        <FormFileUpload
          accept='.xml,.html,application/zip,application/x-rar-compressed'
          onChange={handleFileUpload}
        >
          <i className={fileHasUploaded ? 'rm-icon-trash' : 'rm-icon-export'}></i>
          <span>{fileHasUploaded ? 'Replace File' : 'Add or Upload File'}</span>
        </FormFileUpload>

        {fileHasUploaded && (
          <Button onClick={handleRemoveImage}>
            <i className='rm-icon-trash'></i> <span>Remove File</span>
          </Button>
        )}
      </div>

      <span>File types: xml, hmtl, zip, rar &bull; Max file size: 0.5MB.</span>
    </>
  );

  return (
    <div className='file-uploader__wrapper'>
      <div className='file-uploader__content'>
        {fileIsUploading ? uploadingActions : fileActions}
      </div>
    </div>
  );
}
