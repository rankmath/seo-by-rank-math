/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import Button from './buttons/Button';
import TextControl from './inputs/TextControl';
import TextAreaControl from './inputs/TextAreaControl';
import CustomSelectControl from './select/CustomSelectControl';
import SearchSelectControl from './select/search-select/SearchSelectControl';


const AllComponents = () => {
	return (
		<div className='container'>

			<TextInputFieldsShowcase />

			<ButtonsShowcase />

		</div>
	)
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
