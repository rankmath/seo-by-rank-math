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
import SegmentedSelectControl from './select/SegmentedSelectControl';


const AllComponents = () => {
  return (
    <div className='container'>

      <FileUploadersShowcase />

      <ConnectionStatus />

      <Scores />

      <AnchorTagStatusShowcase />

      <FilterAndSwitchTabs />

      <MenuListShowcase />

      <VerticalTabsShowcase />

      <HorizontalTabs />

      <NoticeShowcase />

      <ControlsShowcase />

      <TextInputFields />

      <ButtonsShowcase />

    </div>
  )
};

function FileUploadersShowcase() {
  return (
    <>
      <h2>FILE UPLOADERS</h2>

      <div className='components-wrapper'>
        <div>
          <h4>Other File Uploader</h4>

          <FileUploader />
        </div>

        <div className="margin-top">
          <h4>Image Uploader</h4>

          <ImageUploader />
        </div>
      </div>
    </>
  )
};

function ConnectionStatus() {
  return (
    <>
      <h2>CONNECTION STATUS</h2>

      <div className='components-wrapper'>

        <div>
          <h4>Light Indicators</h4>

          <div className="components-group">
            <LightIndicator />
            <LightIndicator status='red' />
            <LightIndicator status='yellow' />
            <LightIndicator status='green' />
          </div>
        </div>


        <div>
          <h4>Connection</h4>

          <div className='components-group'>
            <ConnectionButton status='connect'>Connect</ConnectionButton>
            <ConnectionButton status='connected'>Connected</ConnectionButton>
          </div>

          <div className='components-group'>
            <ConnectionButton status='disconnect'>Disconnect</ConnectionButton>
            <ConnectionButton status='disconnected'>Disconnected</ConnectionButton>
          </div>

          <div className='components-group'>
            <ConnectionButton status='reconnect'>Reconnect</ConnectionButton>
            <ConnectionButton status='connected'>Connected</ConnectionButton>
          </div>
        </div>
      </div>
    </>
  )
};

function Scores() {
  return (
    <>
      <h2>SCORES</h2>

      <div className='components-wrapper'>
        <div>
          <h4>Content AI Score Bar</h4>

          <div className="components-group">
            <ContentAIScoreBar value={20} />
          </div>
        </div>


        <div>
          <h4>Snippet Editor Score Bar</h4>

          <div className="components-group">
            <EditorScoreBar value={23} />
          </div>
          <div className="components-group">
            <EditorScoreBar value={52} />
          </div>
          <div className="components-group">
            <EditorScoreBar value={94} />
          </div>
        </div>

        <div>
          <h4 className='margin-top'>Keyword Suggestions</h4>

          <div className='components-group'>
            <KeywordButton />

            <KeywordButton keyword='Increase' severity='neutral' />

            <KeywordButton severity='bad' />
          </div>
        </div>

        <div>
          <h4 className='margin-top'>Rank Math Buttons</h4>

          <div className='components-group'>
            <ScoreButton>94/100</ScoreButton>

            <ScoreButton severity='neutral'>52/100</ScoreButton>

            <ScoreButton severity='bad'>52/100</ScoreButton>
          </div>
        </div>


        <div>
          <h4 className='margin-top'>Content AI Buttons</h4>

          <div className='components-group'>
            <ScoreButton company='Content AI'>94/100</ScoreButton>

            <ScoreButton company='Content AI' severity='neutral'>52/100</ScoreButton>

            <ScoreButton company='Content AI' severity='bad'>52/100</ScoreButton>
          </div>
        </div>
      </div>
    </>
  )
};

function AnchorTagStatusShowcase() {
  return (
    <>
      <h2>ANCHOR TAG STATUS</h2>

      <div className='components-wrapper'>
        <div className='components-group'>
          <AnchorTagStatus>All Good</AnchorTagStatus>

          <AnchorTagStatus severity='warning' children='Warning' />

          <AnchorTagStatus severity='error' children='Error' />

          <AnchorTagStatus severity='grey' children='Label' />
        </div>
      </div>
    </>
  )
};

function FilterAndSwitchTabs() {
  return (
    <>
      <h2>FILTER AND SWITCH TABS</h2>

      <div className='components-wrapper'>
        <div>
          <h4>Switch Tabs - Black</h4>

          <div className='components-group'>
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
                  <p>This is the content for tab {tab.name}</p>
                </div>
              )
              }
            /></div>

          <div className='components-group'>
            <SwitchTaps
              tabs={[
                {
                  name: 'tab5',
                  icon: <TabPanelWithIcon icon='rm-icon-trash' title='Content' />
                },
                {
                  name: 'tab6',
                  icon: <TabPanelWithIcon icon='rm-icon-role-manager' title='Content' />
                }
              ]}
              children={(tab) => (
                <div>
                  <p>This is the content for tab {tab.name}</p>
                </div>
              )
              }
            /></div>
        </div>

        <div>
          <h4>Switch Tabs - Blue</h4>

          <div className='components-group'>
            <SwitchTaps
              iconOnly
              variant='blue'
              tabs={[
                {
                  name: 'tab3',
                  icon: <TabPanelWithIcon icon='rm-icon-trash' title='Content' />
                },
                {
                  name: 'tab4',
                  icon: <TabPanelWithIcon icon='rm-icon-role-manager' title='Content' />
                }
              ]}
              children={(tab) => (
                <div>
                  <p>This is the content for tab {tab.name}</p>
                </div>
              )
              }
            /></div>

          <div className='components-group'>
            <SwitchTaps
              variant='blue'
              tabs={[
                {
                  name: 'tab7',
                  icon: <TabPanelWithIcon icon='rm-icon-trash' title='Content' />
                },
                {
                  name: 'tab8',
                  icon: <TabPanelWithIcon icon='rm-icon-role-manager' title='Content' />
                }
              ]}
              children={(tab) => (
                <div>
                  <p>This is the content for tab {tab.name}</p>
                </div>
              )
              }
            /></div>
        </div>


        <div>
          <h4>Filter Menu - Black</h4>

          <div className='components-group'>
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
                  <h5>{tab.title}</h5>
                  <p>This is the content for tab {tab.name}</p>
                </div>
              )
              }
            /></div>
        </div>


        <div>
          <h4>Filter Menu - Blue</h4>

          <div className='components-group'>
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
                  <h5>{tab.title}</h5>
                  <p>This is the content for tab {tab.name}</p>
                </div>
              )
              }
            /></div>
        </div>
      </div>
    </>
  )
}

function MenuListShowcase() {
  return (
    <>
      <h2>MENU LIST ITEMS</h2>

      <div className='components-wrapper'>
        <div>
          <h4>Choose a Block Popup</h4>

          <div className='components-group'>
            <MenuListPopup
              menuItems={[
                { title: 'Frist Option Item', icon: 'rm-icon-trash' },
                { title: 'Second Option Item', icon: 'rm-icon-trash' },
                { title: 'Third Option Item', icon: 'rm-icon-trash' },
                { title: 'Fourth Option Item', icon: 'rm-icon-trash' },
              ]}
            />
          </div>
        </div>

        <div>
          <h4>Sidebar Menu List</h4>

          <div className='components-group'>
            <SidebarMenuList
              menuItems={[
                { title: 'Frist Option List Item', icon: 'rm-icon-trash' },
                { title: 'Second Option List Item', icon: 'rm-icon-trash' },
                { title: 'Third Option List Item', icon: 'rm-icon-trash' },
                { title: 'Fourth Option List Item', icon: 'rm-icon-trash' },
              ]}
            />
          </div>
        </div>
      </div>
    </>
  )
};

function VerticalTabsShowcase() {
  return (
    <>
      <h2>VERTICAL TABS</h2>

      <div className='components-wrapper'>
        <div>
          <h4>Single Section Column</h4>

          <div className='components-group'>
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
        </div>

        <div>
          <h4>Multi Section Column </h4>

          <div className='components-group'>
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
          </div>
        </div>
      </div>
    </>
  )
};

function HorizontalTabs() {
  return (
    <div>
      <h2>HORIZONTAL TABS</h2>

      <div className='components-wrapper'>
        <h4>Sidebar Tabs</h4>

        <div>
          <h4>Variant 1</h4>
          <div className='components-group'>
            <SidebarTabPanel
              tabs={[
                {
                  name: 'tab1',
                  icon: <TabPanelWithIcon icon='rm-icon-trash' title='First' />
                },
                {
                  name: 'tab2',
                  icon: <TabPanelWithIcon icon='rm-icon-trash' title='Second' />
                },
                {
                  name: 'tab3',
                  icon: <TabPanelWithIcon icon='rm-icon-trash' title='Third' />
                }
              ]}
              children={(tab) => (
                <div>
                  <h5>{tab.title}</h5>
                  <p>This is the content for tab {tab.name}</p>
                </div>
              )
              }
            />
          </div>

          <h4>Variant 2</h4>
          <div className='components-group'>
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
                  <h5>{tab.title}</h5>
                  <p>This is the content for tab {tab.name}</p>
                </div>
              )
              }
            />
          </div>
        </div>


        <div>
          <h4>Page & Modal Tabs</h4>

          <div className='components-group'>
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
        </div>
      </div>
    </div>
  )
};

function NoticeShowcase() {
  return (
    <>
      <h2>NOTICE BANNERS</h2>

      <div className='components-wrapper'>
        <div className='notice-group'>
          <Notice
            actions={[
              {
                label: "Read More",
                url: "https://wordpress.org"
              }
            ]}
          >
            Lorem ipsum dolor sit, amet consectetur ipsum dolor sit consectetur ipsum.
          </Notice>

          <Notice
            icon='rm-icon-trash'
            actions={[
              {
                label: "Read More",
                url: "https://wordpress.org"
              }
            ]}
          >
            Lorem ipsum dolor sit, amet consectetur ipsum dolor sit consectetur ipsum.
          </Notice>

          <Notice
            status='warning'
            actions={[
              {
                label: "Read More",
                url: "https://wordpress.org"
              }
            ]}
          >
            Lorem ipsum dolor sit, amet consectetur ipsum dolor sit consectetur ipsum.
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
            Lorem ipsum dolor sit, amet consectetur ipsum dolor sit consectetur ipsum.
          </Notice>

          <Notice
            status='success'
            actions={[
              {
                label: "Read More",
                url: "https://wordpress.org"
              }
            ]}
          >
            Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum dolor sit consectetur ipsum.
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
            Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum dolor sit consectetur ipsum.
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
            Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum dolor sit consectetur ipsum.
          </Notice>

          <Notice
            status='error'
            icon='rm-icon-trash'
            actions={[
              {
                label: "Read More",
                url: "https://wordpress.org"
              }
            ]}
          >
            Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum dolor sit consectetur ipsum.
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
            Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum dolor sit consectetur ipsum.
          </Notice>

          <Notice
            status='info-grey'
            icon='rm-icon-trash'
            actions={[
              {
                label: "Read More",
                url: "https://wordpress.org"
              }
            ]}
          >
            Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum dolor sit consectetur ipsum.
          </Notice>
        </div>
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
    <div>
      <h2>CONTROLS</h2>

      <div className='components-wrapper'>
        <div>
          <h4>Select/ Deselect All</h4>

          <div className='components-group'>
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
        </div>

        <div>
          <h4>Select One</h4>
          <div className="components-group">
            <CheckboxControl
              label="Checkbox"
              checked={isChecked}
              onChange={setChecked}
            // disabled
            />
          </div>
        </div>

        <div>
          <h4>Radio Buttons</h4>

          <div className='components-group'>
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
        </div>

        <div>
          <h4>Toggle</h4>

          <div className='components-group'>
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
        </div>
      </div>
    </div>
  );
};

function TextInputFields() {
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
    <div>
      <h2>INPUT FIELDS</h2>

      <div className='components-wrapper'>
        <div>
          <h4>Dropdown Select 3</h4>

          <div className='components-group'>
            <SearchSelectControl
              value={value}
              options={optionsList}
              onChange={
                ({ selectedItem: { key, name } }) => setValue({ key, name: name.props.title })
              }
            // disabled
            />
          </div>
        </div>

        <div>
          <h4>Dropdown Select 2</h4>

          <div className='components-group'>
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
        </div>

        <div>
          <h4>Dropdown Select 1</h4>

          <div className='components-group'>
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
        </div>

        <div>
          <h4>Text Area</h4>

          <div className='components-group'>
            <TextAreaControl placeholder='Placeholder Text' />
            <TextAreaControl placeholder='Disabled Field' disabled />
          </div>
        </div>

        <div>
          <h4>Stepper</h4>

          <div className='components-group'>
            <TextControl type='number' placeholder='Placeholder Text' />
          </div>
        </div>

        <div>
          <h4>Single Line</h4>

          <div className='components-group'>
            <TextControl placeholder='Placeholder Text' />
            <TextControl placeholder='Disabled Field' disabled />
          </div>

          <div className='components-group'>
            <TextControl isSuccess />
            <TextControl value='email@website.com' isError />
          </div>
        </div>
      </div>
    </div>
  )
};

function ButtonsShowcase() {
  return (
    <div>
      <h2>BUTTONS</h2>

      <div>
        <h4>Primary</h4>

        <div className='components-wrapper'>
          <div className='button-group'>
            <Button size='small'>Label</Button>

            <Button icon='rm-icon-trash' iconPosition='right'>Label</Button>

            <Button size='large' children={'Label'} />

            <Button size='large' disabled>Label</Button>
          </div>
        </div>
      </div>

      <div>
        <h4>Primary Outline</h4>

        <div className='components-wrapper'>
          <div className='button-group'>
            <Button variant='primary-outline' size='small'>Label</Button>

            <Button variant='primary-outline' icon='rm-icon-trash'>Label</Button>

            <Button variant='primary-outline' size='large'>Label</Button>

            <Button variant='primary-outline' size='large' disabled>Label</Button>
          </div>
        </div>
      </div>

      <div>
        <h4>Secondary</h4>

        <div className='components-wrapper'>
          <div className='button-group'>
            <Button variant='secondary' size='small'>Label</Button>

            <Button variant='secondary' icon='rm-icon-trash'>Label</Button>

            <Button variant='secondary' size='large'>Label</Button>

            <Button variant='secondary' size='large' disabled>Label</Button>
          </div>
        </div>
      </div>

      <div>
        <h4>Secondary Grey</h4>

        <div className='components-wrapper'>
          <div className='button-group'>
            <Button variant='secondary-grey' size='small'>Label</Button>

            <Button variant='secondary-grey' icon='rm-icon-trash'>Label</Button>

            <Button variant='secondary-grey' size='large'>Label</Button>

            <Button variant='secondary-grey' size='large' disabled>Label</Button>
          </div>
        </div>
      </div>

      <div>
        <h4>Tertiary Outline</h4>

        <div className='components-wrapper'>
          <div className='button-group'>
            <Button variant='tertiary-outline' size='small'>Label</Button>

            <Button variant='tertiary-outline' icon='rm-icon-trash'>Label</Button>

            <Button variant='tertiary-outline' size='large'>Label</Button>

            <Button variant='tertiary-outline' size='large' disabled>Label</Button>
          </div>
        </div>
      </div>

      <div>
        <h4>Tertiary</h4>

        <div className='components-wrapper'>
          <div className='button-group'>
            <Button variant='tertiary' icon='rm-icon-trash' size='small' />

            <Button variant='tertiary' icon='rm-icon-trash' />

            <Button variant='tertiary' icon='rm-icon-trash' size='large' />

            <Button variant='tertiary' icon='rm-icon-trash' size='large' disabled />
          </div>
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
