/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import PageTabPanel from './tabs/horizontal-tabs/PageTabPanel';
import TabPanelWithIcon from './tabs/horizontal-tabs/TabPanelWithIcon';
import SidebarTabPanel from './tabs/horizontal-tabs/SidebarTabPanel';
import FilterMenus from './tabs/FilterMenus';
import SingleSectionTabPanel from './tabs/vertical-tabs/SingleSectionTabPanel';
import MultiSectionTabPanel from './tabs/vertical-tabs/MultiSectionTabPanel';
import SwitchTaps from './tabs/SwitchTaps';

const AllComponents = () => {
  return (
    <div className='components-container'>
      <SwitchTaps
        iconOnly
        tabs={[
          {
            name: 'tab1',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='Content' />
          },
          {
            name: 'tab2',
            icon: <TabPanelWithIcon icon='rm-icon-role-manager' title='Content' />
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

      <div style={{ margin: '1rem' }} />

      <SwitchTaps
        iconOnly
        variant='blue'
        tabs={[
          {
            name: 'tab1',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='Content' />
          },
          {
            name: 'tab2',
            icon: <TabPanelWithIcon icon='rm-icon-role-manager' title='Content' />
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

      <div style={{ margin: '1rem' }} />

      <SwitchTaps
        tabs={[
          {
            name: 'tab1',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='Content' />
          },
          {
            name: 'tab2',
            icon: <TabPanelWithIcon icon='rm-icon-role-manager' title='Content' />
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

      <div style={{ margin: '1rem' }} />

      <SwitchTaps
        variant='blue'
        tabs={[
          {
            name: 'tab1',
            icon: <TabPanelWithIcon icon='rm-icon-trash' title='Content' />
          },
          {
            name: 'tab2',
            icon: <TabPanelWithIcon icon='rm-icon-role-manager' title='Content' />
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

      <div style={{ margin: '1rem' }} />

      <FilterMenus
        tabs={[
          {
            name: 'tab1',
            title: 'All',
          },
          {
            name: 'tab2',
            title: 'First Content',
          },
          {
            name: 'tab3',
            title: 'Third',
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

      <div style={{ margin: '1rem' }} />

      <FilterMenus
        variant='blue'
        tabs={[
          {
            name: 'tab1',
            title: 'All',
          },
          {
            name: 'tab2',
            title: 'First Content',
          },
          {
            name: 'tab3',
            title: 'Third',
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

      <div style={{ margin: '1rem' }} />

      <PageTabPanelShowcase />

      <div style={{ margin: '1rem' }} />

      <SidebarTabPanelShowcase />
    </div>
  )
};

function PageTabPanelShowcase() {
  return (
    <div>
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

function SidebarTabPanelShowcase() {

  return (
    <div>
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
