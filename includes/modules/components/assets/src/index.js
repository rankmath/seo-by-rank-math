/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import FileUploader from './uploaders/FileUploader';
import ImageUploader from './uploaders/ImageUploader';
import LightIndicator from './LightIndicator';
import ContentAIScoreBar from './score-bars/ContentAIScoreBar';
import EditorScoreBar from './score-bars/EditorScoreBar';
import ConnectionButton from './buttons/ConnectionButton';
import AnchorTagStatus from './buttons/AnchorTagStatus';
import ScoreButton from './buttons/ScoreButton';
import KeywordButton from './buttons/KeywordButton';
import SwitchTaps from './tabs/SwitchTaps';
import FilterMenus from './tabs/FilterMenus';
import MenuListPopup from './tabs/menu-lists/MenuListPopup';
import SidebarMenuList from './tabs/menu-lists/SidebarMenuList';
import SingleSectionTabPanel from './tabs/vertical-tabs/SingleSectionTabPanel';
import MultiSectionTabPanel from './tabs/vertical-tabs/MultiSectionTabPanel';
import TabPanelWithIcon from './tabs/TabPanelWithIcon';
import PageTabPanel from './tabs/horizontal-tabs/PageTabPanel';
import SidebarTabPanel from './tabs/horizontal-tabs/SidebarTabPanel';
import Notice from './prompts/Notice';
import SearchSelectControl from './select/search-select/SearchSelectControl';
import CheckboxControl from './controls/CheckboxControl';
import RadioControl from './controls/RadioControl';
import ToggleControl from './controls/ToggleControl';
import CustomSelectControl from './select/CustomSelectControl';
import TextAreaControl from './inputs/TextAreaControl';
import TextControl from './inputs/TextControl';
import Button from './buttons/Button';


const AllComponents = () => {
  return (
    <div className='components-container'>

      <FileUploadersShowcase />

      <LightIndicatorShowcase />

      <ScoreBarShowcase />

      <ScoreAndStatusButtons />

      <FilterAndSwitchTabs />

      <TabsAndMenuListShowcase />

      <PageTabPanelShowcase />

      <SidebarTabPanelShowcase />

      <NoticeShowcase />

      <SearchSelectShowcase />

      <ControlsShowcase />

      <CustomSelectControlShowcase />

      <TextAreaControlShowcase />

      <TextControlShowcase />

      <ButtonsShowcase />

    </div>
  )
};

function FileUploadersShowcase() {
  return (
    <>

      <div className="group-components">
        <FileUploader />
      </div>

      <div className="group-components">
        <ImageUploader />
      </div>

    </>
  )
};

function LightIndicatorShowcase() {
  return (
    <>

      <div className="group-components">
        <LightIndicator />
        <LightIndicator status='red' />
        <LightIndicator status='yellow' />
        <LightIndicator status='green' />
      </div>

    </>
  )
};

function ScoreBarShowcase() {
  return (
    <div style={{ paddingTop: '5rem' }}>

      <div className="group-components">
        <ContentAIScoreBar value={20} />
      </div>


      <div className="group-components">
        <EditorScoreBar value={23} />
      </div>
      <div className="group-components">
        <EditorScoreBar value={52} />
      </div>
      <div className="group-components">
        <EditorScoreBar value={94} />
      </div>

    </div>
  )
};

function ScoreAndStatusButtons() {
  return (
    <>
      <div>
        <h3>Connection Button</h3>

        <div className='group-components'>
          <ConnectionButton status='connect'>Connect</ConnectionButton>
          <ConnectionButton status='connected'>Connected</ConnectionButton>
        </div>

        <div className='group-components'>
          <ConnectionButton status='disconnect'>Disconnect</ConnectionButton>
          <ConnectionButton status='disconnected'>Disconnected</ConnectionButton>
        </div>

        <div className='group-components'>
          <ConnectionButton status='reconnect'>Reconnect</ConnectionButton>
          <ConnectionButton status='connected'>Connected</ConnectionButton>
        </div>
      </div>

      <div>
        <h3>Anchor Tag Status</h3>

        <div className='group-components'>
          <AnchorTagStatus>All Good</AnchorTagStatus>

          <AnchorTagStatus severity='warning' children='Warning' />

          <AnchorTagStatus severity='error' children='Error' />

          <AnchorTagStatus severity='grey' children='Label' />
        </div>
      </div>

      <div>
        <h3>Score Button</h3>

        <div className='group-components'>
          <ScoreButton>94/100</ScoreButton>

          <ScoreButton severity='neutral'>52/100</ScoreButton>

          <ScoreButton severity='bad'>52/100</ScoreButton>
        </div>

        <div className='group-components'>
          <ScoreButton company='Content AI'>94/100</ScoreButton>

          <ScoreButton company='Content AI' severity='neutral'>52/100</ScoreButton>

          <ScoreButton company='Content AI' severity='bad'>52/100</ScoreButton>
        </div>
      </div>

      <div>
        <h3>Keyword Button</h3>

        <div className='group-components'>
          <KeywordButton />

          <KeywordButton keyword='Increase' severity='neutral' />

          <KeywordButton severity='bad' />
        </div>
      </div>

    </>
  )
};

function FilterAndSwitchTabs() {
  return (
    <>
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
    </>
  )
}

function TabsAndMenuListShowcase() {
  return (
    <>
      <MenuListPopup
        label='Menu List Popup'
        menuItems={[
          { title: 'Frist Option Item', icon: 'rm-icon-trash' },
          { title: 'Second Option Item', icon: 'rm-icon-trash' },
          { title: 'Third Option Item', icon: 'rm-icon-trash' },
          { title: 'Fourth Option Item', icon: 'rm-icon-trash' },
        ]}
      />

      <div style={{ margin: '1rem' }} />

      <SidebarMenuList
        label='Sidebar Menu List Popup'
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

      <div style={{ margin: '1rem' }} />

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
    </>
  )
};

function PageTabPanelShowcase() {
  return (
    <div style={{ padding: '1rem 0' }}>
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

function NoticeShowcase() {
  return (
    <>
      <div className='notice-group'>
        <Notice
        >
          Lorem ipsum dolor sit, amet consectetur adipisicing elit.
        </Notice>

        <Notice
          status='warning'
          icon='rm-icon-trash'
          actions={[
            {
              label: "Read More",
              url: "https://wordpress.org"
            }
          ]}
        >
          Lorem ipsum dolor sit, amet consectetur ipsum dolor sit.
        </Notice>

        <Notice
          status='success'
          icon='rm-icon-trash'
          actions={[
            {
              label: "Read More",
              url: "https://wordpress.org"
            }
          ]}
        >
          Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum dolor sit.
        </Notice>

        <Notice
          status='error'
          actions={[
            {
              label: "Read More",
              url: "https://wordpress.org"
            }
          ]}
        >
          Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum dolor sit.
        </Notice>

        <Notice
          status='info-grey'
          actions={[
            {
              label: "Read More",
              url: "https://wordpress.org"
            }
          ]}
        >
          Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum dolor sit.
        </Notice>
      </div>
    </>
  )
};

function SearchSelectShowcase() {
  const optionsList = [
    {
      key: 'first_option',
      name: {
        title: 'First Option Title',
        subTitle: '%code_text%',
        description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint qui amet corporis ipsum? Nam, adipisci iste!'
      }
    },
    {
      key: 'second_option',
      name: {
        title: 'Second Option Title',
        subTitle: '%code_text%',
        description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint qui amet corporis ipsum? Nam, adipisci iste!'
      }
    },
  ]

  const [value, setValue] = useState({
    key: optionsList[0].key,
    name: optionsList[0].name.title,
  });

  return (
    <>
      <div style={{ padding: '1rem 0' }}>
        <SearchSelectControl
          label="Search Options"
          value={value}
          options={optionsList}
          onChange={
            ({ selectedItem: { key, name } }) => setValue({ key, name: name.props.title })
          }
        // disabled
        />
      </div>
    </>
  )
};

function ControlsShowcase() {
  const [value, setValue] = useState(false);
  const [option, setOption] = useState('a');
  const [isChecked, setChecked] = useState(true);

  const initialCheckboxes = [
    { id: 'checkbox-1', label: 'Checkbox 1', checked: false },
    { id: 'checkbox-2', label: 'Checkbox 2', checked: false },
    { id: 'checkbox-3', label: 'Checkbox 3', checked: false },
  ];

  const [checkboxes, setCheckboxes] = useState(initialCheckboxes);

  const handleCheckboxChange = (id) => {
    const updatedCheckboxes = checkboxes.map((checkbox) =>
      checkbox.id === id ? { ...checkbox, checked: !checkbox.checked } : checkbox
    );
    setCheckboxes(updatedCheckboxes);
  };

  const areAllChecked = checkboxes.every((checkbox) => checkbox.checked);
  const areAnyChecked = checkboxes.some((checkbox) => checkbox.checked);

  const handleMasterCheckboxChange = () => {
    const newCheckValue = !areAllChecked;
    const updatedCheckboxes = checkboxes.map((checkbox) => ({
      ...checkbox,
      checked: newCheckValue,
    }));
    setCheckboxes(updatedCheckboxes);
  };

  return (
    <>
      <div className='group-components'>
        <CheckboxControl
          label="Select All"
          checked={areAllChecked}
          indeterminate={!areAllChecked && areAnyChecked}
          isIndeterminate
          onChange={handleMasterCheckboxChange}
        // disabled
        />
        {checkboxes.map((checkbox) => (
          <CheckboxControl
            key={checkbox.id}
            label={checkbox.label}
            checked={checkbox.checked}
            onChange={() => handleCheckboxChange(checkbox.id)}
          // disabled
          />
        ))}
      </div>

      <div className="group-components">
        <CheckboxControl
          label="Checkbox"
          checked={isChecked}
          onChange={setChecked}
        // disabled
        />
      </div>

      <div className='group-components'>
        <RadioControl
          label="Radio"
          selected={option}
          options={[
            { label: 'Selected', value: 'a' },
            { label: 'Default', value: 'e' },
          ]}
          onChange={(value) => setOption(value)}
        // disabled
        />
      </div>

      <div className='group-components'>
        <ToggleControl
          help='Toggle'
          label="On"
          checked={value}
          onChange={() => setValue((state) => !state)}
        />

        <ToggleControl
          label="Off"
          help='Toggle'
          checked={value}
          onChange={() => setValue((state) => !state)}
          disabled
        />
      </div>
    </>
  );
};

function CustomSelectControlShowcase() {
  const [size, setSize] = useState({
    key: "large",
    name: "Large",
  });

  const options = [
    {
      key: "thumbnail",
      name: "Thumbnail",
    },
    {
      key: "medium",
      name: "Medium",
    },
    {
      key: "large",
      name: "Large",
    },
    {
      key: "full",
      name: "Full Size",
    }
  ]

  return (
    <>
      <div className='group-components'>
        <CustomSelectControl
          label="Label"
          value={size}
          options={options}
          onChange={(target) => setSize(target.selectedItem)}
        />
        <CustomSelectControl
          label="Label"
          value={size}
          options={options}
          onChange={(target) => setSize(target.selectedItem)}
          disabled
        />
      </div>

      <div className='group-components'>
        <CustomSelectControl
          value={size}
          options={options}
          onChange={(target) => setSize(target.selectedItem)}
        />
        <CustomSelectControl
          value={size}
          options={options}
          onChange={(target) => setSize(target.selectedItem)}
          disabled
        />
      </div>
    </>
  )
}

function TextAreaControlShowcase() {
  return (
    <>
      <div className='group-components'>
        <TextAreaControl placeholder='Placeholder Text' />
        <TextAreaControl placeholder='Disabled Field' disabled />
      </div>
    </>
  )
}

function TextControlShowcase() {
  return (
    <>
      <div className='group-components'>
        <TextControl type='number' placeholder='Placeholder Text' />
        <TextControl placeholder='Placeholder Text' />
        <TextControl placeholder='Disabled Field' disabled />
      </div>

      <div className='group-components'>
        <TextControl isSuccess />
        <TextControl value='email@website.com' isError />
      </div>
    </>
  )
}

function ButtonsShowcase() {
  return (
    <div>
      <h4>Text Buttons</h4>

      <div>
        <h3>Primary</h3>

        <div className='group-components'>
          <Button size='small'>Label</Button>

          <Button icon='rm-icon-trash' iconPosition='right'>Label</Button>

          <Button size='large' children={'Label'} />

          <Button size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Primary Outline</h3>

        <div className='group-components'>
          <Button variant='primary-outline' size='small'>Label</Button>

          <Button variant='primary-outline' icon='rm-icon-trash'>Label</Button>

          <Button variant='primary-outline' size='large'>Label</Button>

          <Button variant='primary-outline' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Secondary</h3>

        <div className='group-components'>
          <Button variant='secondary' size='small'>Label</Button>

          <Button variant='secondary' icon='rm-icon-trash'>Label</Button>

          <Button variant='secondary' size='large'>Label</Button>

          <Button variant='secondary' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Secondary Grey</h3>

        <div className='group-components'>
          <Button variant='secondary-grey' size='small'>Label</Button>

          <Button variant='secondary-grey' icon='rm-icon-trash'>Label</Button>

          <Button variant='secondary-grey' size='large'>Label</Button>

          <Button variant='secondary-grey' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Tertiary Outline</h3>

        <div className='group-components'>
          <Button variant='tertiary-outline' size='small'>Label</Button>

          <Button variant='tertiary-outline' icon='rm-icon-trash'>Label</Button>

          <Button variant='tertiary-outline' size='large'>Label</Button>

          <Button variant='tertiary-outline' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Tertiary</h3>

        <div className='group-components'>
          <Button variant='tertiary' size='small'>Label</Button>

          <Button variant='tertiary' icon='rm-icon-trash'>Label</Button>

          <Button variant='tertiary' size='large'>Label</Button>

          <Button variant='tertiary' size='large' disabled>Label</Button>
        </div>
      </div>
    </div>
  );
}


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
