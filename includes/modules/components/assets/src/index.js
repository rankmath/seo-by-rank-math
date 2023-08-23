/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';
import AnchorTagStatus from './buttons/AnchorTagStatus';
import ScoreButton from './buttons/ScoreButton';
import KeywordButton from './buttons/KeywordButton';
import ConnectionButton from './buttons/ConnectionButton';


const AllComponents = () => {
  return (
    <div className='components-container'>
      <div>
        <h3>Connection Button</h3>

        <div className='group-components'>
          <ConnectionButton status='connect'>Connect</ConnectionButton>
          <ConnectionButton status='connected'>Connected</ConnectionButton>
        </div>

        <div className='group-components'>
          <ConnectionButton status='disconnect'>Disconnect</ConnectionButton>
          <ConnectionButton status='disconnected'>Disconnected</ConnectionButton>
        </div>

        <div className='group-components'>
          <ConnectionButton status='reconnect'>Reconnect</ConnectionButton>
          <ConnectionButton status='connected'>Connected</ConnectionButton>
        </div>
      </div>

      <div>
        <h3>Anchor Tag Status</h3>

        <div className='group-components'>
          <AnchorTagStatus>All Good</AnchorTagStatus>

          <AnchorTagStatus severity='warning' children='Warning' />

          <AnchorTagStatus severity='error' children='Error' />

          <AnchorTagStatus severity='grey' children='Label' />
        </div>
      </div>

      <div>
        <h3>Score Button</h3>

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

      <div>
        <h3>Keyword Button</h3>

        <div className='group-components'>
          <KeywordButton />

          <KeywordButton keyword='Increase' severity='neutral' />

          <KeywordButton severity='bad' />
        </div>
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
