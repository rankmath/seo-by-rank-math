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

const AllComponents = () => {
	return (
		<div className='container'>

			<ButtonsShowcase />

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
