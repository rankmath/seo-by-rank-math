/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import CheckboxControl from './controls/CheckboxControl';
import RadioControl from './controls/RadioControl';
import ToggleControl from './controls/ToggleControl';
import Button from './buttons/Button';


const AllComponents = () => {
  return (
    <div className='components-container'>
      {/* <div style={{ margin: '1rem' }} /> */}

      <ButtonsShowcase />
    </div>
  )
};

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
