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
import ContentAIScoreBar from './score-bars/ContentAIScoreBar';
import SingleSectionTabPanel from './tabs/vertical-tabs/SingleSectionTabPanel';
import MultiSectionTabPanel from './tabs/vertical-tabs/MultiSectionTabPanel';
import SelectControl from './select/SelectControl';
import TextAreaControl from './inputs/TextAreaControl';
import TextControl from './inputs/TextControl';
import ToggleControl from './controls/ToggleControl';
import RadioControl from './controls/RadioControl';
import CheckboxControl from './controls/CheckboxControl';
import ImageUploader from './uploaders/ImageUploader';
import FileUploader from './uploaders/FileUploader';
import Notice from './prompts/Notice';
import ScoreButton from './buttons/ScoreButton';
import KeywordButton from './buttons/KeywordButton';
import AnchorTagStatus from './buttons/AnchorTagStatus';
import ConnectionButton from './buttons/ConnectionButton';
import LightIndicator from './prompts/LightIndicator';
import EditorScoreBar from './score-bars/EditorScoreBar';
import FilterMenus from './tabs/FilterMenus';
import TabPanelWithIcon from './tabs/TabPanelWithIcon';
import SwitchTaps from './tabs/SwitchTaps';
import PageTabPanel from './tabs/horizontal-tabs/PageTabPanel';
import SidebarTabPanel from './tabs/horizontal-tabs/SidebarTabPanel';
import SidebarMenuList from './tabs/menu-lists/SidebarMenuList';
import MenuListPopup from './tabs/menu-lists/MenuListPopup';
import SelectControlWithSearchbox from './select/SelectControlWithSearchbox';
import SegmentedSelectControl from './select/SegmentedSelectControl';

const AllComponents = () => {
	return (
		<div className='container'>

			<ButtonsShowcase />

			<InputFieldsShowcase />

			<ControlsShowcase />

			<UploadersShowcase />

			<NoticeShowcase />

			<ScoresShowcase />

			<HorizontalTabsShowcase />

			<VerticalTabsShowcase />

			<FilterAndSwitchTabsShowcase />

			<MenuListShowcase />

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
function InputFieldsShowcase() {
	// Dropdown Select 1
	const select1Options = [
		{
			key: "first_option",
			name: "Option Default",
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
	const select1DisabledOptions = [
		{
			key: "first_option",
			name: "Option Disabled",
		}
	]
	const [select1Value, setSelect1Value] = useState(select1Options[0]);
	const [disabledSelect1Value, setDisabledSelect1Value] = useState(select1DisabledOptions[0]);


	// Dropdown Select 2
	const select2Options = [
		{
			key: "first_option",
			name: "Default Option",
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
	const select2DisabledOptions = [
		{
			key: "first_option",
			name: "Option Disabled",
		}
	]
	const [select2Value, setSelect2Value] = useState(select2Options[0]);
	const [disabledSelect2Value, setDisabledSelect2Value] = useState(select2DisabledOptions[0]);


	// Dropdown Select 3
	const select3Options = [
		{
			key: 'first_option',
			name: {
				title: 'First Option Title',
				subTitle: '%code_text%',
				description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio porro doloremque non, debitis aliquid dolores ad, nobis natus!'
			}
		},
		{
			key: 'second_option',
			name: {
				title: 'Second Option Title',
				subTitle: '%code_text%',
				description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio porro doloremque non, debitis aliquid dolores ad, nobis natus!'
			}
		},
		{
			key: 'third_option',
			name: {
				title: 'Third Option Title',
				subTitle: '%code_text%',
				description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio porro doloremque non, debitis aliquid dolores ad, nobis natus!'
			}
		},
		{
			key: 'fourth_option',
			name: {
				title: 'Fourth Option Title',
				subTitle: '%code_text%',
				description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio porro doloremque non, debitis aliquid dolores ad, nobis natus!'
			}
		},
	]
	const select3DisabledOptions = [
		{
			key: 'option_disabled',
			name: {
				title: 'Option Disabled',
				subTitle: '%code_text%',
				description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio porro doloremque non, debitis aliquid dolores ad, nobis natus!'
			}
		}
	]

	const [select3Value, setSelect3Value] = useState({
		key: select3Options[0].key,
		name: select3Options[0].name.title
	});
	const [disabledSelect3Value, setDisabledSelect3Value] = useState({
		key: select3DisabledOptions[0].key,
		name: select3DisabledOptions[0].name.title
	});


	// Dropdown Select 3 Alternative
	const select3OptionsAlt = [
		{
			title: 'First Option Title',
			subTitle: '%code_text%',
			description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio nobis natus doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint!'
		},
		{

			title: 'Second Option Title',
			subTitle: '%code_text%',
			description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio nobis natus doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint!'

		},
		{

			title: 'Third Option Title',
			subTitle: '%code_text%',
			description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio nobis natus doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint!'

		},
		{

			title: 'Fourth Option Title',
			subTitle: '%code_text%',
			description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio nobis natus doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint!'

		},
	]
	const select3DisabledOptionsAlt = [
		{
			title: 'Option Disabled',
			subTitle: '%code_text%',
			description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio nobis natus doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint!'
		}
	]
	const [select3ValueAlt, setSelect3ValueAlt] = useState(select3OptionsAlt[0].title);
	const [disabledSelect3ValueAlt, setDisabledSelect3ValueAlt] = useState(select3DisabledOptionsAlt[0].title);

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
							options={select1Options}
							value={select1Value}
							onChange={({ selectedItem }) => setSelect1Value(selectedItem)}
						/>
						<SelectControl
							options={select1DisabledOptions}
							value={disabledSelect1Value}
							onChange={({ selectedItem }) => setDisabledSelect1Value(selectedItem)}
							disabled
						/>
					</div>
				</div>

				<div>
					<h4>Dropdown Select 2</h4>

					<div className='components-group'>
						<SelectControl
							label="Label"
							options={select2Options}
							value={select2Value}
							onChange={({ selectedItem }) => setSelect2Value(selectedItem)}
						/>
						<SelectControl
							label="Label"
							options={select2DisabledOptions}
							value={disabledSelect2Value}
							onChange={({ selectedItem }) => setDisabledSelect2Value(selectedItem)}
							disabled
						/>
					</div>
				</div>

				<div>
					<h4>Dropdown Select 3</h4>

					<div>
						<h4 style={{ color: 'red' }}>BUGGY. throws an error when you type into the searchbox</h4>

						<div className='components-group'>
							<SelectControl
								options={select3Options}
								value={select3Value}
								onChange={
									({ selectedItem: { key, name } }) => setSelect3Value({ key, name: name.props.title })
								}
								withSearch
							/>
						</div>
						<div className='components-group'>
							<SelectControl
								options={select3DisabledOptions}
								value={disabledSelect3Value}
								onChange={
									({ selectedItem: { key, name } }) => setDisabledSelect3Value({ key, name: name.props.title })
								}
								withSearch
								disabled
							/>
						</div>
					</div>

					<div>
						<h4 style={{ color: 'green' }}>WORKS. Custom alternative. Only "chevron-down" icon missen</h4>

						<div className='components-group'>
							<SelectControlWithSearchbox
								options={select3OptionsAlt}
								value={select3ValueAlt}
								onChange={setSelect3ValueAlt}
							/>
						</div>

						<div className='components-group'>
							<SelectControlWithSearchbox
								options={select3DisabledOptionsAlt}
								value={disabledSelect3ValueAlt}
								onChange={setDisabledSelect3ValueAlt}
								disabled
							/>
						</div>
					</div>
				</div>
			</div>
		</div>
	)
};
function ControlsShowcase() {
	// Segmented Select Control
	const [segmentedSelectValue, setSegmentedSelectValue] = useState('option_1');
	const [disabledSegmentedSelect, setDisabledSegmentedSelect] = useState('disabled_1');
	const [segmentedSelectValue2, setSegmentedSelectValue2] = useState('option2_1');

	const segmentedSelectOptions = [
		{ label: 'Select Option 1', value: 'option_1' },
		{ label: 'Select Option 2', value: 'option_2' },
		{ label: 'Select Option 3', value: 'option_3' }
	]
	const disabledSegmentedSelectOptions = [
		{ label: 'Disabled Option 1', value: 'disabled_1' },
		{ label: 'Disabled Option 2', value: 'disabled_2' },
		{ label: 'Disabled Option 3', value: 'disabled_3' }
	]
	const segmentedSelectOptions2 = [
		{ label: 'Option 1', value: 'option2_1' },
		{ label: 'Option 2', value: 'option2_2' }
	]


	// Toggle Control
	const [toggleCheckedValue, setToggleCheckedValue] = useState(false);
	const [toggleDisabledCheckedValue, setToggleDisabledCheckedValue] = useState(false);
	const handleToggleChange = () => {
		setToggleCheckedValue((state) => !state);
	}
	const handleDisabledToggleChange = () => {
		setToggleDisabledCheckedValue((state) => !state);
	}


	// Radio Control
	const [selectedRadioOption, setSelectedRadioOption] = useState('selected');
	const [disabledRadioOption, setDisabledRadioOption] = useState('');
	const radioOptions = [
		{ label: 'Default', value: 'default' },
		{ label: 'Selected', value: 'selected' }
	];
	const disabledRadioOptions = [
		{ label: 'Disabled', value: 'disabled' }
	];


	// Checkbox Control
	const initialCheckboxes = [
		{ id: 'checkbox-1', label: 'Checkbox 1', checked: false },
		{ id: 'checkbox-2', label: 'Checkbox 2', checked: false },
		{ id: 'checkbox-3', label: 'Checkbox 3', checked: false },
	];
	const [checkboxes, setCheckboxes] = useState(initialCheckboxes);
	const [checkboxIsChecked, setCheckboxIsChecked] = useState(false);
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
	const handleCheckboxChange = (id) => {
		const updatedCheckboxes = checkboxes.map((checkbox) =>
			checkbox.id === id ? { ...checkbox, checked: !checkbox.checked } : checkbox
		);
		setCheckboxes(updatedCheckboxes);
	};

	return (
		<div>
			<h2>CONTROLS</h2>

			<div className='components-wrapper'>
				<div>
					<h4>Segmented</h4>

					<div className='components-group'>
						<SegmentedSelectControl
							toggleOptions={segmentedSelectOptions}
							value={segmentedSelectValue}
							onChange={setSegmentedSelectValue}
						/>
					</div>

					<div className='components-group'>
						<SegmentedSelectControl
							toggleOptions={disabledSegmentedSelectOptions}
							value={disabledSegmentedSelect}
							onChange={setDisabledSegmentedSelect}
							disabled
						/>
					</div>

					<div className='components-group'>
						<SegmentedSelectControl
							toggleOptions={segmentedSelectOptions2}
							value={segmentedSelectValue2}
							onChange={setSegmentedSelectValue2}
						/>
					</div>
				</div>

				<div>
					<h4>Toggle</h4>

					<div className='components-group'>
						<ToggleControl
							label="Off"
							checked={toggleCheckedValue}
							onChange={handleToggleChange}
						/>
						<ToggleControl
							label="On"
							checked={true}
							onChange={() => { }}
						/>

						<ToggleControl
							label="Disabled"
							checked={toggleDisabledCheckedValue}
							onChange={handleDisabledToggleChange}
							disabled
						/>
					</div>
				</div>

				<div>
					<h4>Radio Buttons</h4>

					<div className='components-group'>
						<RadioControl
							selected={selectedRadioOption}
							options={radioOptions}
							onChange={setSelectedRadioOption}
						/>

						<RadioControl
							selected={disabledRadioOption}
							options={disabledRadioOptions}
							onChange={setDisabledRadioOption}
							disabled
						/>
					</div>
				</div>

				<div>
					<h4>Checkboxes</h4>

					<div>
						<h4>Select One</h4>
						<div className="components-group">
							<CheckboxControl
								label="Default"
								checked={checkboxIsChecked}
								onChange={setCheckboxIsChecked}
							/>

							<CheckboxControl
								label="Checked"
								checked={true}
							/>

							<CheckboxControl
								label="Disabled"
								checked={false}
								disabled
							/>
						</div>
					</div>

					<div>
						<h4>Select/ Deselect All</h4>

						<div className='components-group'>
							<CheckboxControl
								label="Default"
								checked={areAllChecked}
								indeterminate={!areAllChecked && areAnyChecked}
								isIndeterminate
								onChange={handleMasterCheckboxChange}
							/>
							{checkboxes.map((checkbox) => (
								<CheckboxControl
									key={checkbox.id}
									label={checkbox.label}
									checked={checkbox.checked}
									onChange={() => handleCheckboxChange(checkbox.id)}
								/>
							))}

							<CheckboxControl
								label="Disabled"
								checked={false}
								onChange={() => { }}
								disabled
							/>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};
function UploadersShowcase() {
	// Image Uploader
	const [imageUploadingPercentage, setImageUploadingPercentage] = useState(0);
	const [imageIsUploading, setImageIsUploading] = useState(false);
	const [imageUploadComplete, setImageUploadComplete] = useState(false);
	const [uploadedImageSrc, setUploadedImageSrc] = useState(null);

	const handleImageUpload = (file) => {
		setImageIsUploading(true);

		// Simulates upload progress
		const interval = setInterval(() => {
			setImageUploadingPercentage((prevPercentage) => {
				const newPercentage = prevPercentage + 25;
				if (newPercentage >= 100) {
					clearInterval(interval);
					setImageIsUploading(false);
					setImageUploadComplete(true);
				}
				return newPercentage;
			});
		}, 500);

		setUploadedImageSrc(URL.createObjectURL(file));
	};
	const handleRemoveImage = () => {
		setImageUploadComplete(false);
		setUploadedImageSrc(null);
	};


	// File Uploader
	const [fileIsUploadingPercentage, setFileIsUploadingPercentage] = useState(0);
	const [fileIsUploading, setFileIsUploading] = useState(false);
	const [fileUploadComplete, setFileUploadComplete] = useState(false);
	const [uploadedFileName, setUploadedFileName] = useState('');

	const handleFileUpload = (file) => {
		setFileIsUploading(true);

		const interval = setInterval(() => {
			setFileIsUploadingPercentage((prevPercentage) => {
				const newPercentage = prevPercentage + 25;
				if (newPercentage >= 100) {
					clearInterval(interval);
					setFileIsUploading(false);
					setFileUploadComplete(true);
					setUploadedFileName(file.name);
				}
				return newPercentage;
			});
		}, 500);
	}
	const handleFileRemove = () => {
		setFileUploadComplete(false);
		setUploadedFileName(null);
	}

	return (
		<>
			<h2>FILE UPLOADERS</h2>

			<div className='components-wrapper'>
				<div>
					<h4>Image Uploader</h4>

					<ImageUploader
						{...{
							uploadedImageSrc,
							imageUploadingPercentage,
							imageIsUploading,
							imageUploadComplete,
							maxFileSize: 500000,
							onImageUpload: handleImageUpload,
							onImageRemove: handleRemoveImage
						}}
					/>
				</div>

				<div className="margin-top">
					<h4>Other File Uploader</h4>

					<FileUploader
						{...{
							fileUploadComplete,
							fileIsUploading,
							fileIsUploadingPercentage,
							uploadedFileName,
							maxFileSize: 500000,
							onFileRemove: handleFileRemove,
							onFileUpload: handleFileUpload
						}}
					/>
				</div>
			</div>
		</>
	)
};
function NoticeShowcase() {
	const noticeAction = [
		{
			label: "Read More",
			url: "https://wordpress.org"
		}
	];

	return (
		<>
			<h2>NOTICE BANNERS</h2>

			<div className='components-wrapper'>
				<div className='notice-group'>
					<div>
						<h4>Error</h4>

						<div>
							<Notice
								status='error'
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum.
							</Notice>

							<div className='margin-top-sm' />

							<Notice
								status='error'
								icon='rm-icon-trash'
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing ipsum dolor sit consectetur elit ipsum dolor sit consectetur ipsum.
							</Notice>
						</div>
					</div>


					<div>
						<h4>Warning</h4>

						<div>
							<Notice
								status='warning'
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum.
							</Notice>

							<div className='margin-top-sm' />

							<Notice
								status='warning'
								icon='rm-icon-trash'
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing ipsum dolor sit consectetur elit ipsum dolor sit consectetur ipsum.
							</Notice>
						</div>
					</div>


					<div>
						<h4>Success</h4>

						<div>
							<Notice
								status='success'
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum.
							</Notice>

							<div className='margin-top-sm' />

							<Notice
								status='success'
								icon='rm-icon-trash'
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing ipsum dolor sit consectetur elit ipsum dolor sit consectetur ipsum.
							</Notice>
						</div>
					</div>


					<div>
						<h4>Info</h4>

						<div>
							<Notice
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum.
							</Notice>

							<div className='margin-top-sm' />

							<Notice
								icon='rm-icon-trash'
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing ipsum dolor sit consectetur elit ipsum dolor sit consectetur ipsum.
							</Notice>
						</div>
					</div>


					<div>
						<h4>Info Grey</h4>

						<div>
							<Notice
								status='info-grey'
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing elit ipsum.
							</Notice>

							<div className='margin-top-sm' />

							<Notice
								status='info-grey'
								icon='rm-icon-trash'
								actions={noticeAction}
							>
								Lorem ipsum dolor sit, amet consectetur adipisicing ipsum dolor sit consectetur elit ipsum dolor sit consectetur ipsum.
							</Notice>
						</div>
					</div>
				</div>
			</div>
		</>
	)
};
function ScoresShowcase() {
	return (
		<>
			<h2>SCORES</h2>

			<div className='components-wrapper'>
				<div>
					<h4>Rank Math Buttons</h4>

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
					<h4>Content AI Score Bar</h4>

					<div className="components-group">
						<ContentAIScoreBar value={2} />
					</div>

					<div className="components-group margin-top">
						<ContentAIScoreBar value={52} />
					</div>

					<div className="components-group margin-top">
						<ContentAIScoreBar value={98} />
					</div>
				</div>

				<div>
					<h4 className='margin-top'>Keyword Suggestions</h4>

					<div className='components-group'>
						<KeywordButton score='94' keyword='Keyword' />

						<KeywordButton score='52' keyword='Keyword' />

						<KeywordButton score='23' keyword='Keyword' />
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
					<h4>Light Indicators</h4>

					<div className="components-group">
						<LightIndicator status='green' />
						<LightIndicator status='yellow' />
						<LightIndicator status='red' />
						<LightIndicator />
					</div>
				</div>
			</div>
		</>
	)
};
function HorizontalTabsShowcase() {
	const sidebarTabWithIcon = [
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
	];
	const sidebarTabTextOnly = [
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
	];
	const pageTabPanelTabs = [
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
			icon: <TabPanelWithIcon icon='rm-icon-misc' title='Forth Tab' />
		}
	];

	return (
		<div>
			<h2>HORIZONTAL TABS</h2>

			<div className='components-wrapper'>
				<h4>Sidebar Tabs</h4>

				<div>
					<h4>Variant 1</h4>
					<div className='components-group'>
						<SidebarTabPanel
							tabs={sidebarTabWithIcon}
							children={(tab) => (
								<div>
									<h5>{tab.title}</h5>
									<p>This is the content for tab {tab.name}</p>
								</div>
							)}
						/>
					</div>

					<h4>Variant 2</h4>
					<div className='components-group'>
						<SidebarTabPanel
							tabs={sidebarTabTextOnly}
							children={(tab) => (
								<div>
									<h5>{tab.title}</h5>
									<p>This is the content for tab {tab.name}</p>
								</div>
							)}
						/>
					</div>
				</div>


				<div>
					<h4>Page & Modal Tabs</h4>

					<div className='components-group'>
						<PageTabPanel
							tabs={pageTabPanelTabs}
						/>
					</div>
				</div>
			</div>
		</div>
	)
};
function VerticalTabsShowcase() {
	const tabItems = [
		{ title: 'Frist Tab', icon: 'rm-icon-settings' },
		{ title: 'Second Tab', icon: 'rm-icon-settings' },
		{ title: 'Third Tab', icon: 'rm-icon-settings' },
		{ title: 'Fourth Tab', icon: 'rm-icon-settings' },
		{ title: 'Fifth Tab', icon: 'rm-icon-settings' },
		{ title: 'Sixth Tab', icon: 'rm-icon-settings' },
		{ title: 'Seventh Tab', icon: 'rm-icon-settings' },
		{ title: 'Eight Tab', icon: 'rm-icon-settings' },
		{ title: 'Ninth Tab', icon: 'rm-icon-settings' },
	];

	return (
		<>
			<h2>VERTICAL TABS</h2>

			<div className='components-wrapper'>
				<div>
					<h4>Single Section Column</h4>

					<div className='components-group'>
						<SingleSectionTabPanel
							menuItems={tabItems}
						/>
					</div>
				</div>

				<div>
					<h4>Multi Section Column </h4>

					<div className='components-group'>
						<MultiSectionTabPanel>
							<SingleSectionTabPanel
								menuItems={tabItems.slice(0, 4)}
							/>

							<SingleSectionTabPanel
								label='Section Title'
								menuItems={tabItems.slice(4, 7)}
							/>

							<SingleSectionTabPanel
								label='Section Title'
								menuItems={[
									...tabItems.slice(7, 9),
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
function FilterAndSwitchTabsShowcase() {
	const filterMenuTabs = [
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
	];

	return (
		<>
			<h2>FILTER AND SWITCH TABS</h2>

			<div className='components-wrapper'>
				<div>
					<h4>Filter Menu - Black</h4>

					<div className='components-group'>
						<FilterMenus
							tabs={filterMenuTabs}
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
							tabs={filterMenuTabs}
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
					<h4>Switch Tabs - Black</h4>

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
						/>
					</div>

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
						/>
					</div>
				</div>

				<div>
					<h4>Switch Tabs - Blue</h4>

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
						/>
					</div>

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
						/>
					</div>
				</div>
			</div>
		</>
	)
}
function MenuListShowcase() {
	const icon = 'rm-icon-settings'

	const sidebarMenuListItems = [
		{ title: 'Frist Option List Item', icon },
		{ title: 'Second Option List Item', icon },
		{ title: 'Third Option List Item', icon },
		{ title: 'Fourth Option List Item', icon },
		{ title: 'Fifth Option List Item', icon },
		{ title: 'Sixth Option List Item', icon },
	];

	const menuListPopupItems = [
		{ title: 'Frist Option Item', icon },
		{ title: 'Second Option Item', icon },
		{ title: 'Fourth Option Item', icon },
		{ title: 'Fifth Option Item', icon },
		{ title: 'Sixth Option Item', icon },
		{ title: 'Third Option Item', icon },
		{ title: 'Second Option Item', icon },
		{ title: 'Third Option Item', icon },
	];

	return (
		<>
			<h2>MENU LIST ITEMS</h2>

			<div className='components-wrapper'>
				<div>
					<h4>Sidebar Menu List</h4>

					<div className='components-group'>
						<SidebarMenuList
							menuItems={sidebarMenuListItems}
						/>
					</div>
				</div>


				<div>
					<h4>Choose a Block Popup</h4>

					<div className='components-group'>
						<MenuListPopup
							menuItems={menuListPopupItems}
						/>
					</div>
				</div>
			</div>
		</>
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
