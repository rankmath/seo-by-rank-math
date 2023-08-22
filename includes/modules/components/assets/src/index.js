/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import ScoreButton from './buttons/ScoreButton';


const AllComponents = () => {
  return (
    <div className='components-container'>

      <div className='group-components'>
        <ScoreButton>94/100</ScoreButton>

        <ScoreButton severity='neutral'>52/100</ScoreButton>

        <ScoreButton severity='bad'>52/100</ScoreButton>
      </div>

      <div className='group-components'>
        <ScoreButton company='Content AI'>94/100</ScoreButton>

        <ScoreButton company='Content AI' severity='neutral'>52/100</ScoreButton>

        <ScoreButton company='Content AI' severity='bad'>52/100</ScoreButton>
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
