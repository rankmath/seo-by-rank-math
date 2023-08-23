/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import ConnectionStatusButton from './buttons/ConnectionStatusButton';
import AnchorTagStatus from './buttons/AnchorTagStatusButton';


const AllComponents = () => {
  return (
    <div className='components-container'>

      <div className='group-components'>
        <ConnectionStatusButton iconName='rm-icon-plus'>Connect</ConnectionStatusButton>
        <ConnectionStatusButton>Disconnect</ConnectionStatusButton>
        <ConnectionStatusButton>Reconnect</ConnectionStatusButton>
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
