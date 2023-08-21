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
import SidebarMenuList from './tabs/SidebarMenuList';


const AllComponents = () => {
  return (
    <div className='components-container'>
      <SidebarMenuList
        menuItems={[
          { title: 'Frist Option List Item', icon: 'rm-icon-trash' },
          { title: 'Second Option List Item', icon: 'rm-icon-trash' },
          { title: 'Third Option List Item', icon: 'rm-icon-trash' },
          { title: 'Fourth Option List Item', icon: 'rm-icon-trash' },
        ]}
      />

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
