/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import TabPanelWithIcon from './tabs/TabPanelWithIcon';
import PageTabPanel from './tabs/PageTabPanel';
import Button from './buttons/Button';
import Notice from './prompts/Notice';


const AllComponents = () => {
  return (
    <div className='components-container'>
      <PageTabPanelShowcase />
    </div>
  )
};

function PageTabPanelShowcase() {
  return (
    <div className='components-container'>
      <PageTabPanel
        tabs={[
          {
            name: 'tab1',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='First Tab' />
          },
          {
            name: 'tab2',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='Second Tab' />
          },
          {
            name: 'tab3',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='Third Tab' />
          }
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
