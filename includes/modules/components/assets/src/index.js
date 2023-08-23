/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import AnchorTagStatus from './buttons/AnchorTagStatus';


const AllComponents = () => {
  return (
    <div className='components-container'>

      <div className='group-components'>
        <AnchorTagStatus>All Good</AnchorTagStatus>

        <AnchorTagStatus severity='warning' children='Warning' />

        <AnchorTagStatus severity='error' children='Error' />

        <AnchorTagStatus severity='grey' children='Label' />
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
