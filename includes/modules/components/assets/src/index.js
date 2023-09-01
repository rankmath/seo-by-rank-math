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
import SwitchTaps from './tabs/SwitchTaps';
import TabPanelWithIcon from './tabs/TabPanelWithIcon';
import FilterMenus from './tabs/FilterMenus';

const AllComponents = () => {
	return (
		<div className='container'>

			<FilterAndSwitchTabsShowcase />

			<ControlsShowcase />

			<TextInputFieldsShowcase />

			<ButtonsShowcase />

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
					<div>
						<h4>Primary</h4>
						<div className='button-group'>
							<Button size='small'>Label</Button>

							<Button>Label</Button>

							<Button size='large' children={'Label'} />

							<Button size='large' disabled>Label</Button>
						</div>
					</div>

					<div className='margin-top'>
						<h4>Primary Outline</h4>

						<div className='button-group'>
							<Button variant='primary-outline' size='small'>Label</Button>

							<Button variant='primary-outline'>Label</Button>

							<Button variant='primary-outline' size='large'>Label</Button>

							<Button variant='primary-outline' size='large' disabled>Label</Button>
						</div>
					</div>

					<div className='margin-top'>
						<h4>Secondary</h4>

						<div className='button-group'>
							<Button variant='secondary' size='small'>Label</Button>

							<Button variant='secondary'>Label</Button>

							<Button variant='secondary' size='large'>Label</Button>

							<Button variant='secondary' size='large' disabled>Label</Button>
						</div>
					</div>

					<div className='margin-top'>
						<h4>Secondary Grey</h4>

						<div className='button-group'>
							<Button variant='secondary-grey' size='small'>Label</Button>

							<Button variant='secondary-grey'>Label</Button>

							<Button variant='secondary-grey' size='large'>Label</Button>

							<Button variant='secondary-grey' size='large' disabled>Label</Button>
						</div>
					</div>

					<div className='margin-top'>
						<h4>Tertiary Outline</h4>

						<div className='button-group'>
							<Button variant='tertiary-outline' size='small'>Label</Button>

							<Button variant='tertiary-outline'>Label</Button>

							<Button variant='tertiary-outline' size='large'>Label</Button>

							<Button variant='tertiary-outline' size='large' disabled>Label</Button>
						</div>
					</div>
				</div>
			</div>

			<div>
				<h3>ICON + TEXT BUTTONS</h3>

				<div className='components-wrapper'>
					<div>
						<h4>Primary</h4>

						<div className='button-group'>
							<Button size='small' icon='rm-icon-rank-math'>Label</Button>

							<Button icon='rm-icon-rank-math'>Label</Button>

							<Button size='large' icon='rm-icon-rank-math'>Label</Button>

							<Button size='large' icon='rm-icon-rank-math' disabled>Label</Button>
						</div>
					</div>

					<div className='margin-top'>
						<h4>Primary Outline</h4>

						<div className='button-group'>
							<Button variant='primary-outline' size='small' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='primary-outline' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='primary-outline' size='large' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='primary-outline' size='large' icon='rm-icon-rank-math' disabled>Label</Button>
						</div>
					</div>

					<div className='margin-top'>
						<h4>Secondary</h4>

						<div className='button-group'>
							<Button variant='secondary' size='small' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='secondary' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='secondary' size='large' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='secondary' size='large' icon='rm-icon-rank-math' disabled>Label</Button>
						</div>
					</div>

					<div className='margin-top'>
						<h4>Secondary Grey</h4>

						<div className='button-group'>
							<Button variant='secondary-grey' size='small' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='secondary-grey' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='secondary-grey' size='large' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='secondary-grey' size='large' icon='rm-icon-rank-math' disabled>Label</Button>
						</div>
					</div>

					<div className='margin-top'>
						<h4>Tertiary Outline</h4>

						<div className='button-group'>
							<Button variant='tertiary-outline' size='small' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='tertiary-outline' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='tertiary-outline' size='large' icon='rm-icon-rank-math'>Label</Button>

							<Button variant='tertiary-outline' size='large' icon='rm-icon-rank-math' disabled>Label</Button>
						</div>
					</div>
				</div>
			</div>

			<div>
				<h3>ICON BUTTONS</h3>

				<div className='components-wrapper'>
					<div>
						<h4>Primary</h4>

						<div className='button-group'>
							<Button size='small' icon='rm-icon-rank-math' />

							<Button icon='rm-icon-rank-math' />

							<Button size='large' icon='rm-icon-rank-math' />

							<Button size='large' icon='rm-icon-rank-math' disabled />
						</div>
					</div>

					<div className='margin-top'>
						<h4>Primary Outline</h4>

						<div className='button-group'>
							<Button variant='primary-outline' size='small' icon='rm-icon-rank-math' />

							<Button variant='primary-outline' icon='rm-icon-rank-math' />

							<Button variant='primary-outline' size='large' icon='rm-icon-rank-math' />

							<Button variant='primary-outline' size='large' icon='rm-icon-rank-math' disabled />
						</div>
					</div>

					<div className='margin-top'>
						<h4>Secondary</h4>

						<div className='button-group'>
							<Button variant='secondary' size='small' icon='rm-icon-rank-math' />

							<Button variant='secondary' icon='rm-icon-rank-math' />

							<Button variant='secondary' size='large' icon='rm-icon-rank-math' />

							<Button variant='secondary' size='large' icon='rm-icon-rank-math' disabled />
						</div>
					</div>

					<div className='margin-top'>
						<h4>Icon Buttons</h4>

						<div className='button-group'>
							<Button variant='secondary-grey' size='small' icon='rm-icon-rank-math' />

							<Button variant='secondary-grey' icon='rm-icon-rank-math' />

							<Button variant='secondary-grey' size='large' icon='rm-icon-rank-math' />

							<Button variant='secondary-grey' size='large' icon='rm-icon-rank-math' disabled />
						</div>
					</div>

					<div className='margin-top'>
						<h4>Tertiary Outline</h4>

						<div className='button-group'>
							<Button variant='tertiary-outline' size='small' icon='rm-icon-rank-math' />

							<Button variant='tertiary-outline' icon='rm-icon-rank-math' />

							<Button variant='tertiary-outline' size='large' icon='rm-icon-rank-math' />

							<Button variant='tertiary-outline' size='large' icon='rm-icon-rank-math' disabled />
						</div>
					</div>

					<div className='margin-top'>
						<h4>Tertiary</h4>
						<div className='button-group'>
							<Button variant='tertiary' icon='rm-icon-rank-math' size='small' />

							<Button variant='tertiary' icon='rm-icon-rank-math' />

							<Button variant='tertiary' icon='rm-icon-rank-math' size='large' />

							<Button variant='tertiary' icon='rm-icon-rank-math' size='large' disabled />
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
