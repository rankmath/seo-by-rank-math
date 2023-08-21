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
import MultiSectionTabPanel from './tabs/vertical-tabs/MultiSectionTabPanel';


const AllComponents = () => {
  return (
    <div className='components-container'>
      <MultiSectionTabPanel>
        <SingleSectionTabPanel
          menuItems={[
            { title: 'Frist Tab', icon: 'rm-icon-trash' },
            { title: 'Second Tab', icon: 'rm-icon-trash' },
            { title: 'Third Tab', icon: 'rm-icon-trash' },
            { title: 'Fourth Tab', icon: 'rm-icon-trash' },
          ]}
        />

        <SingleSectionTabPanel
          label='Settings'
          menuItems={[
            { title: 'Settings 1', icon: 'rm-icon-trash' },
            { title: 'Settings 2', icon: 'rm-icon-trash' },
            { title: 'Settings 3', icon: 'rm-icon-trash' },
            { title: 'Settings 4', icon: 'rm-icon-trash' },
          ]}
        />
      </MultiSectionTabPanel>

      <div style={{ margin: '1rem' }} />

      <SingleSectionTabPanel
        label='Title'
        menuItems={[
          { title: 'Frist Tab', icon: 'rm-icon-trash' },
          { title: 'Second Tab', icon: 'rm-icon-trash' },
          { title: 'Third Tab', icon: 'rm-icon-trash' },
          { title: 'Fourth Tab', icon: 'rm-icon-trash' },
        ]}
      />
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
