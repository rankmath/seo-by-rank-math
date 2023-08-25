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
    <div className='image-uploader-container'>
      <div>
        <FormFileUpload
          accept="image/*"
        >
          Add or Upload Image
        </FormFileUpload>

        <span>Min: 1400x1400px. &bull; Max: 3000x3000px. &bull; Max file size: 0.5MB.</span>
      </div>

      <img src="" alt="" />
    </div>
  )
}
