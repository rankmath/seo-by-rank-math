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
        <ConnectionStatusButton>Connect</ConnectionStatusButton>
        <ConnectionStatusButton status='connected'>Connected</ConnectionStatusButton>
      </div>

      <div className='group-components'>
        <ConnectionStatusButton status='disconnect'>Disconnect</ConnectionStatusButton>
        <ConnectionStatusButton status='disconnected'>Disconnected</ConnectionStatusButton>
      </div>

      <div className='group-components'>
        <ConnectionStatusButton status='reconnect'>Reconnect</ConnectionStatusButton>
        <ConnectionStatusButton status='connected'>Connected</ConnectionStatusButton>
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
