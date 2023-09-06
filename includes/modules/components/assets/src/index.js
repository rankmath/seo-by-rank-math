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

const AllComponents = () => {
	return (
		<div className='container'>

			<VerticalTabsShowcase />

			<ScoresShowcase />

			<ButtonsShowcase />

		</div>
	)
};

function VerticalTabsShowcase() {
	return (
		<>
			<h2>VERTICAL TABS</h2>

			<div className='components-wrapper'>
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
			</div>
		</>
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
