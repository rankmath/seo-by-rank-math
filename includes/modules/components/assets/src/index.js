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

      <div className='group-button'>
        <Button size='small'>Label</Button>
        <Button>Label</Button>
        <Button size='large'>Label</Button>
        <Button size='large' disabled>Label</Button>
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
