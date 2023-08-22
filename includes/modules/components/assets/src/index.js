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
import KeywordSuggestion from './buttons/KeywordSuggestion';


const AllComponents = () => {
  return (
    <div className='components-container'>

      <div className='group-components'>
        <KeywordSuggestion />

        <KeywordSuggestion severity='neutral' />

        <KeywordSuggestion severity='bad' />
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
