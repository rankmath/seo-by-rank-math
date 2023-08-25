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
  const [fileUploadingPercentage, setFileUploadingPercentage] = useState(50);
  const [fileIsUploading, setFileIsUploading] = useState(false);
  const [fileUploaded, setFileUploaded] = useState(false);
  const [uploadedFileName, setUploadedFileName] = useState('filename.xml');

  const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
      if (file.size > 500000) {
        alert("File size exceeds 0.5MB.");
        return;
      }
    }
  };

  const uploadingActions = (
    <>
      <RangeControl
        value={fileUploadingPercentage}
        min={0}
        max={100}
        withInputField={false}
        showTooltip={false}
      />

      <span className='file-uploader__is-uploading'>Uploading File: {fileUploadingPercentage}%</span>
    </>
  );

  const fileActions = (
    <>
      {fileUploaded && (
        <div>
          <i className='rm-icon-trash'></i> <span>File added: </span> <span>{uploadedFileName}</span>
        </div>
      )}

      <div className='file-uploader__actions'>
        <FormFileUpload accept='image/*'>
          <i className={fileUploaded ? 'rm-icon-trash' : 'rm-icon-export'}></i>
          <span>{fileUploaded ? 'Replace File' : 'Add or Upload File'}</span>
        </FormFileUpload>

        {fileUploaded && (
          <Button>
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
