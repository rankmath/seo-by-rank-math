/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import SingleSectionTabPanel from './tabs/vertical-tabs/SingleSectionTabPanel';


const AllComponents = () => {
  return (
    <div className='components-container'>
      <SingleSectionTabPanel
        menuItems={[
          { title: 'Frist Tab', icon: 'rm-icon-trash' },
          { title: 'Second Tab', icon: 'rm-icon-trash' },
          { title: 'Third Tab', icon: 'rm-icon-trash' },
          { title: 'Fourth Tab', icon: 'rm-icon-trash' },
        ]}
      />

      {/* <div style={{ margin: '1rem' }} /> */}
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
