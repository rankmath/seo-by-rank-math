/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/image-uploader.scss'

/**
 * WordPress dependencies
 */
import { RangeControl, FormFileUpload } from '@wordpress/components';

export default function () {
  return (
    <div className='image-uploader'>
      <div className='image-uploader__controls'>
        <FormFileUpload
          accept="image/*"
        >
          <i className='rm-icon-export'></i> <span>Add or Upload Image</span>
        </FormFileUpload>

        <span>Min: 1400x1400px. &bull; Max: 3000x3000px. &bull; Max file size: 0.5MB.</span>
      </div>

      <div className='image-uploader__preview'>
        <span className='image-uploader__preview-title'>Image Preview</span>
        {/* <img src="" alt="" /> */}
      </div>
    </div>
  )
}
