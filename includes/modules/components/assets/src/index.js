/**
 * External dependencies
 */
import { Fragment, createElement, render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Button from './buttons/Button';
import '../scss/components.scss'

const AllComponents = () => {
  return (
    <Fragment>
      <h4>Text Buttons</h4>

      <div>
        <h3>Primary</h3>

        <div className='group-button'>
          <Button size='small'>Label</Button>
          <Button>Label</Button>
          <Button size='large'>Label</Button>
          <Button size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Primary Outline</h3>

        <div className='group-button'>
          <Button variant='primary-outline' size='small'>Label</Button>
          <Button variant='primary-outline'>Label</Button>
          <Button variant='primary-outline' size='large'>Label</Button>
          <Button variant='primary-outline' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Secondary</h3>

        <div className='group-button'>
          <Button variant='secondary' size='small'>Label</Button>
          <Button variant='secondary'>Label</Button>
          <Button variant='secondary' size='large'>Label</Button>
          <Button variant='secondary' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Secondary Grey</h3>

        <div className='group-button'>
          <Button variant='secondary-grey' size='small'>Label</Button>
          <Button variant='secondary-grey'>Label</Button>
          <Button variant='secondary-grey' size='large'>Label</Button>
          <Button variant='secondary-grey' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Tertiary</h3>

        <div className='group-button'>
          <Button variant='tertiary' size='small'>Label</Button>
          <Button variant='tertiary'>Label</Button>
          <Button variant='tertiary' size='large'>Label</Button>
          <Button variant='tertiary' size='large' disabled>Label</Button>
        </div>
      </div>
    </Fragment>
  );
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
