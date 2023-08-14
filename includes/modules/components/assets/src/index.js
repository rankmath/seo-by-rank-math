/**
 * WordPress dependencies
 */
import { createElement, render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import '../scss/components.scss'
import TextControl from './inputs/TextControl';

const AllComponents = () => {
  return (
    <div className='group-components'>
      <TextControl placeholder='Placeholder Text' />
      <TextControl placeholder='Disabled Field' disabled />
    </div>
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
