/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import SidebarTabPanel from './tabs/SidebarTabPanel';
import TabPanelWithIcon from './tabs/TabPanelWithIcon';


const AllComponents = () => {

  return (
    <div className='components-container'>
      <SidebarTabPanel
        tabs={[
          {
            name: 'tab1',
            title: 'First',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='First' />
          },
          {
            name: 'tab2',
            title: 'Second',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='Second' />
          },
          {
            name: 'tab3',
            title: 'Third',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='Third' />
          }
        ]}
        children={(tab) => (
          <div>
            <h3>{tab.title}</h3>
            <p>This is the content for tab {tab.name}</p>
          </div>
        )
        }
      />

      <div style={{ marginTop: '1rem' }}>
        <SidebarTabPanel
          tabs={[
            {
              name: 'tab1',
              title: 'First Tab',
            },
            {
              name: 'tab2',
              title: 'Second Tab',
            },
            {
              name: 'tab3',
              title: 'Third Tab',
            }
          ]}
          children={(tab) => (
            <div>
              <h3>{tab.title}</h3>
              <p>This is the content for tab {tab.name}</p>
            </div>
          )
          }
        />
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
