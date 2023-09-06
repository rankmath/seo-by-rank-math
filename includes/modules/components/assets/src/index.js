/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/presentation.scss';
import '../scss/main.scss';
import Button from './buttons/Button';
import TextControl from './inputs/TextControl';
import TextAreaControl from './inputs/TextAreaControl';
import SelectControl from './select/SelectControl';
import CheckboxControl from './controls/CheckboxControl';
import RadioControl from './controls/RadioControl';
import ToggleControl from './controls/ToggleControl';
import FileUploader from './uploaders/FileUploader';
import ImageUploader from './uploaders/ImageUploader';
import Notice from './prompts/Notice';
import ScoreButton from './buttons/ScoreButton';
import EditorScoreBar from './score-bars/EditorScoreBar';
import ContentAIScoreBar from './score-bars/ContentAIScoreBar';
import KeywordButton from './buttons/KeywordButton';
import AnchorTagStatus from './buttons/AnchorTagStatus';
import ConnectionButton from './buttons/ConnectionButton';
import LightIndicator from './prompts/LightIndicator';
import SidebarTabPanel from './tabs/horizontal-tabs/SidebarTabPanel';
import TabPanelWithIcon from './tabs/TabPanelWithIcon';
import PageTabPanel from './tabs/horizontal-tabs/PageTabPanel';
import SwitchTaps from './tabs/SwitchTaps';
import FilterMenus from './tabs/FilterMenus';
import SingleSectionTabPanel from './tabs/vertical-tabs/SingleSectionTabPanel';
import MultiSectionTabPanel from './tabs/vertical-tabs/MultiSectionTabPanel';
import SidebarMenuList from './tabs/menu-lists/SidebarMenuList';
import MenuListPopup from './tabs/menu-lists/MenuListPopup';

const AllComponents = () => {
	return (
		<div className='container'>

			<MenuListShowcase />

			<VerticalTabsShowcase />

			<HorizontalTabsShowcase />

			<FilterAndSwitchTabsShowcase />

			<ScoresShowcase />

			<NoticeShowcase />

			<FileUploadersShowcase />

			<ControlsShowcase />

			<TextInputFieldsShowcase />

			<ButtonsShowcase />

		</div>
	)
};

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
								{ title: 'Frist Option Item', icon: 'rm-icon-settings' },
								{ title: 'Second Option Item', icon: 'rm-icon-settings' },
								{ title: 'Fourth Option Item', icon: 'rm-icon-settings' },
								{ title: 'Fifth Option Item', icon: 'rm-icon-settings' },
								{ title: 'Sixth Option Item', icon: 'rm-icon-settings' },
								{ title: 'Third Option Item', icon: 'rm-icon-settings' },
								{ title: 'Second Option Item', icon: 'rm-icon-settings' },
								{ title: 'Third Option Item', icon: 'rm-icon-settings' },
							]}
						/>
					</div>
				</div>

				<div>
					<h4>Sidebar Menu List</h4>

					<div className='components-group'>
						<SidebarMenuList
							menuItems={[
								{ title: 'Frist Option List Item', icon: 'rm-icon-settings' },
								{ title: 'Second Option List Item', icon: 'rm-icon-settings' },
								{ title: 'Third Option List Item', icon: 'rm-icon-settings' },
								{ title: 'Fourth Option List Item', icon: 'rm-icon-settings' },
								{ title: 'Fifth Option List Item', icon: 'rm-icon-settings' },
								{ title: 'Sixth Option List Item', icon: 'rm-icon-settings' },
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
							menuItems={[
								{ title: 'Frist Tab', icon: 'rm-icon-settings' },
								{ title: 'Second Tab', icon: 'rm-icon-settings' },
								{ title: 'Third Tab', icon: 'rm-icon-settings' },
								{ title: 'Fourth Tab', icon: 'rm-icon-settings' },
								{ title: 'Fifth Tab', icon: 'rm-icon-settings' },
								{ title: 'Sixth Tab', icon: 'rm-icon-settings' },
								{ title: 'Seventh Tab', icon: 'rm-icon-settings' },
								{ title: 'Eight Tab', icon: 'rm-icon-settings' },
								{ title: 'Ninth Tab', icon: 'rm-icon-settings' },
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
									{ title: 'Frist Tab', icon: 'rm-icon-settings' },
									{ title: 'Second Tab', icon: 'rm-icon-settings' },
									{ title: 'Third Tab', icon: 'rm-icon-settings' },
									{ title: 'Fourth Tab', icon: 'rm-icon-settings' },
								]}
							/>

							<SingleSectionTabPanel
								label='Section Title'
								menuItems={[
									{ title: 'Fifth Tab', icon: 'rm-icon-settings' },
									{ title: 'Sixth Tab', icon: 'rm-icon-settings' },
									{ title: 'Seventh Tab', icon: 'rm-icon-settings' },
								]}
							/>

							<SingleSectionTabPanel
								label='Section Title'
								menuItems={[
									{ title: 'Eight Tab', icon: 'rm-icon-settings' },
									{ title: 'Ninth Tab', icon: 'rm-icon-settings' },
									{ title: 'Tab Panel with Long Double Line Text', icon: 'rm-icon-settings' },
								]}
							/>
						</MultiSectionTabPanel>
					</div>
				</div>
			</div>
		</>
	)
};
function HorizontalTabsShowcase() {
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
									icon: <TabPanelWithIcon icon='rm-icon-settings' title='First' />
								},
								{
									name: 'tab2',
									icon: <TabPanelWithIcon icon='rm-icon-settings' title='Second' />
								},
								{
									name: 'tab3',
									icon: <TabPanelWithIcon icon='rm-icon-category' title='Third' />
								},
								{
									name: 'tab4',
									icon: <TabPanelWithIcon icon='rm-icon-misc' title='Forth' />
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
									icon: <TabPanelWithIcon icon='rm-icon-settings' title='First Tab' />
								},
								{
									name: 'tab2',
									icon: <TabPanelWithIcon icon='rm-icon-settings' title='Second Tab' />
								},
								{
									name: 'tab3',
									icon: <TabPanelWithIcon icon='rm-icon-category' title='Third Tab' />
								},
								{
									name: 'tab4',
									icon: <TabPanelWithIcon icon='rm-icon-misc' title='Fourth Tab' />
								}
							]}
						/>
					</div>
				</div>
			</div>
		</div>
	)
};
function FilterAndSwitchTabsShowcase() {
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
									icon: <TabPanelWithIcon icon='rm-icon-category' title='Content' />
								},
								{
									name: 'tab2',
									icon: <TabPanelWithIcon icon='rm-icon-settings' title='Content' />
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
									icon: <TabPanelWithIcon icon='rm-icon-category' title='Content' />
								},
								{
									name: 'tab6',
									icon: <TabPanelWithIcon icon='rm-icon-settings' title='Content' />
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
									icon: <TabPanelWithIcon icon='rm-icon-category' title='Content' />
								},
								{
									name: 'tab4',
									icon: <TabPanelWithIcon icon='rm-icon-settings' title='Content' />
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
									icon: <TabPanelWithIcon icon='rm-icon-category' title='Content' />
								},
								{
									name: 'tab8',
									icon: <TabPanelWithIcon icon='rm-icon-settings' title='Content' />
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
function ScoresShowcase() {
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
					<h4>Light Indicators</h4>

					<div className="components-group">
						<LightIndicator status='green' />
						<LightIndicator status='yellow' />
						<LightIndicator status='red' />
						<LightIndicator />
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

				<div>
					<h4>ANCHOR TAG STATUS</h4>

					<div className='components-group'>
						<AnchorTagStatus>All Good</AnchorTagStatus>

						<AnchorTagStatus severity='warning' children='2 Warnings' />

						<AnchorTagStatus severity='error' children='5 Errors' />

						<AnchorTagStatus severity='grey' children='Label' />
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

						<ScoreButton severity='bad'>23/100</ScoreButton>
					</div>
				</div>

				<div>
					<h4 className='margin-top'>Content AI Buttons</h4>

					<div className='components-group'>
						<ScoreButton company='Content AI'>94/100</ScoreButton>

						<ScoreButton company='Content AI' severity='neutral'>52/100</ScoreButton>

						<ScoreButton company='Content AI' severity='bad'>23/100</ScoreButton>
					</div>
				</div>
			</div>
		</>
	)
};
function NoticeShowcase() {
	return (
		<>
			<h2>NOTICE BANNERS</h2>

			<div className='components-wrapper'>
				<div className='notice-group'>
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
function ControlsShowcase() {
	const [value, setValue] = useState(false);
	const [option, setOption] = useState('a');
	const [isChecked, setChecked] = useState(false);

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

						<CheckboxControl
							label="Select All"
							checked={areAllChecked}
							indeterminate={!areAllChecked && areAnyChecked}
							isIndeterminate
							onChange={handleMasterCheckboxChange}
							disabled
						/>
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

						<CheckboxControl
							label="Checkbox"
							checked={isChecked}
							onChange={setChecked}
							disabled
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

						<RadioControl
							selected={option}
							options={[
								{ label: 'Default', value: 'e' },
							]}
							onChange={(value) => setOption(value)}
							disabled
						/>
					</div>
				</div>

				<div>
					<h4>Toggle</h4>

					<div className='components-group'>
						<ToggleControl
							label="On"
							checked={value}
							onChange={() => setValue((state) => !state)}
						/>

						<ToggleControl
							label="Off"
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
function TextInputFieldsShowcase() {
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
		{
			key: 'third_option',
			name: {
				title: 'Third Option Title',
				subTitle: '%code_text%',
				description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint qui amet corporis ipsum? Nam, adipisci iste!'
			}
		},
		{
			key: 'fourth_option',
			name: {
				title: 'Fourth Option Title',
				subTitle: '%code_text%',
				description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint qui amet corporis ipsum? Nam, adipisci iste!'
			}
		},
	]
	const options = [
		{
			key: "first_option",
			name: "Frist Option",
		},
		{
			key: "second_option",
			name: "Second Option",
		},
		{
			key: "third_option",
			name: "Third Option",
		}
	]

	const [selectedValue, setSelectedValue] = useState({
		key: optionsList[0].key,
		name: optionsList[0].name.title,
	});

	const [selectedOption, setSelectedOption] = useState(options[0]);

	return (
		<div>
			<h2>INPUT FIELDS</h2>

			<div className='components-wrapper'>
				<div>
					<h4>Single Line</h4>

					<div className='components-group'>
						<TextControl placeholder='Placeholder Text' />
						<TextControl placeholder='Disabled Field' disabled />
					</div>

					<div className='components-group'>
						<TextControl value='email@website.com' isSuccess />
						<TextControl value='email@website.com' isError />
					</div>
				</div>

				<div>
					<h4>Stepper</h4>

					<div className='components-group'>
						<TextControl type='number' placeholder='200' />
						<TextControl type='number' placeholder='200' disabled />
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
					<h4>Dropdown Select 1</h4>

					<div className='components-group'>
						<SelectControl
							value={selectedOption}
							options={options}
							onChange={(target) => setSelectedOption(target.selectedItem)}
						/>
						<SelectControl
							value={selectedOption}
							options={options}
							onChange={(target) => setSelectedOption(target.selectedItem)}
							disabled
						/>
					</div>
				</div>

				<div>
					<h4>Dropdown Select 2</h4>

					<div className='components-group'>
						<SelectControl
							label="Label"
							value={selectedOption}
							options={options}
							onChange={(target) => setSelectedOption(target.selectedItem)}
						/>
						<SelectControl
							label="Label"
							value={selectedOption}
							options={options}
							onChange={(target) => setSelectedOption(target.selectedItem)}
							disabled
						/>
					</div>
				</div>

				<div>
					<h4>Dropdown Select 3</h4>

					<div className='components-group'>
						<SelectControl
							value={selectedValue}
							options={optionsList}
							onChange={
								({ selectedItem: { key, name } }) => setSelectedValue({ key, name: name.props.title })
							}
							withSearch
						/>
					</div>

					<div className='components-group'>
						<SelectControl
							value={selectedValue}
							options={optionsList}
							onChange={
								({ selectedItem: { key, name } }) => setSelectedValue({ key, name: name.props.title })
							}
							withSearch
							disabled
						/>
					</div>
				</div>
			</div>
		</div>
	)
};
function ButtonsShowcase() {
	return (
		<div>
			<div>
				<h3>TEXT BUTTONS</h3>

				<div className='components-wrapper'>
					<div className="button-group">
						<div>
							<p>Primary</p>
							<div>
								<Button size='large' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button size='large'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button size='small'>Label</Button>
							</div>
						</div>

						<div>
							<p>Primary Outline</p>
							<div>
								<Button variant='primary-outline' size='large' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='primary-outline' size='large'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='primary-outline'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='primary-outline' size='small'>Label</Button>
							</div>
						</div>

						<div>
							<p>Secondary</p>
							<div>
								<Button variant='secondary' size='large' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary' size='large'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary' size='small'>Label</Button>
							</div>
						</div>

						<div>
							<p>Secondary Grey</p>
							<div>
								<Button variant='secondary-grey' size='large' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary-grey' size='large'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary-grey'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary-grey' size='small'>Label</Button>
							</div>
						</div>

						<div>
							<p>Tertiary Outline</p>
							<div>
								<Button variant='tertiary-outline' size='large' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary-outline' size='large'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary-outline'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary-outline' size='small'>Label</Button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div>
				<h3>ICON + TEXT BUTTONS</h3>

				<div className='components-wrapper'>
					<div className="button-group">
						<div>
							<p>Primary</p>
							<div>
								<Button size='large' icon='rm-icon-category' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button size='large' icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button size='small' icon='rm-icon-category'>Label</Button>
							</div>
						</div>

						<div>
							<p>Primary Outline</p>
							<div>
								<Button variant='primary-outline' size='large' icon='rm-icon-category' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='primary-outline' size='large' icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='primary-outline' icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='primary-outline' size='small' icon='rm-icon-category'>Label</Button>
							</div>
						</div>

						<div>
							<p>Secondary</p>
							<div>
								<Button variant='secondary' size='large' icon='rm-icon-category' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary' size='large' icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary' icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary' size='small' icon='rm-icon-category'>Label</Button>
							</div>
						</div>

						<div>
							<p>Secondary Grey</p>
							<div>
								<Button variant='secondary-grey' size='large' icon='rm-icon-category' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary-grey' size='large' icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary-grey' icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary-grey' size='small' icon='rm-icon-category'>Label</Button>
							</div>
						</div>

						<div>
							<p>Tertiary Outline</p>
							<div>
								<Button variant='tertiary-outline' size='large' icon='rm-icon-category' disabled>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary-outline' size='large' icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary-outline' icon='rm-icon-category'>Label</Button>
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary-outline' size='small' icon='rm-icon-category'>Label</Button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div>
				<h3>ICON BUTTONS</h3>

				<div className='components-wrapper'>
					<div className="icon-button-group">
						<div>
							<p>Primary</p>
							<div>
								<Button size='large' icon='rm-icon-category' disabled />
							</div>

							<div className='margin-top-sm'>
								<Button size='large' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button size='small' icon='rm-icon-category' />
							</div>
						</div>

						<div>
							<p>Primary Outline</p>
							<div>
								<Button variant='primary-outline' size='large' icon='rm-icon-category' disabled />
							</div>

							<div className='margin-top-sm'>
								<Button variant='primary-outline' size='large' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='primary-outline' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='primary-outline' size='small' icon='rm-icon-category' />
							</div>
						</div>

						<div>
							<p>Secondary</p>
							<div>
								<Button variant='secondary' size='large' icon='rm-icon-category' disabled />
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary' size='large' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary' size='small' icon='rm-icon-category' />
							</div>
						</div>

						<div>
							<p>Secondary Grey</p>
							<div>
								<Button variant='secondary-grey' size='large' icon='rm-icon-category' disabled />
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary-grey' size='large' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary-grey' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='secondary-grey' size='small' icon='rm-icon-category' />
							</div>
						</div>

						<div>
							<p>Tertiary Outline</p>
							<div>
								<Button variant='tertiary-outline' size='large' icon='rm-icon-category' disabled />
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary-outline' size='large' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary-outline' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary-outline' size='small' icon='rm-icon-category' />
							</div>
						</div>

						<div>
							<p>Tertiary</p>
							<div>
								<Button variant='tertiary' size='large' icon='rm-icon-category' disabled />
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary' size='large' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary' icon='rm-icon-category' />
							</div>

							<div className='margin-top-sm'>
								<Button variant='tertiary' size='small' icon='rm-icon-category' />
							</div>
						</div>
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
