/**
 * External dependencies
 */
import { createElement, render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ButtonShowcase } from './buttons/Button';
import '../scss/components.scss'

const AllComponents = () => {
  return (
    <>
      <ButtonShowcase />
    </>
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
