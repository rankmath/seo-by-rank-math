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


const AllComponents = () => {
  return (
    <div className='components-container'>

      <div className="group-components">
        <EditorScoreBar value={23} />
      </div>
      <div className="group-components">
        <EditorScoreBar value={52} />
      </div>
      <div className="group-components">
        <EditorScoreBar value={94} />
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
