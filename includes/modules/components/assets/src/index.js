/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import ImageUploader from './uploaders/ImageUploader';
import FileUploader from './uploaders/FileUploader';


const AllComponents = () => {
  return (
    <div className='components-container'>

      <div className="group-components">
        <FileUploader />
      </div>
      <div className="group-components">
        <ImageUploader />
      </div>

    </div>
  )
};


const renderAllComponents = () => {
  const componentsUi = document.getElementById('components-page');
  if (componentsUi) {
    render(
      createElement(AllComponents),
      componentsUi
    );
  }
};

// Display in UI
renderAllComponents();
