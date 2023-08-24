/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import EditorScoreBar from './score-bars/EditorScoreBar';
import ContentAIScoreBar from './score-bars/ContentAIScoreBar';
import LightIndicator from './LightIndicator';


const AllComponents = () => {
  return (
    <div className='components-container'>

      <div className="group-components">
        <LightIndicator />
        <LightIndicator status='red' />
        <LightIndicator status='yellow' />
        <LightIndicator status='green' />
      </div>

    </div>
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
