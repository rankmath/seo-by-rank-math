/**
 * WordPress dependencies
 */
import { createElement, render, useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import SearchSelectControl from './select/search-select/SearchSelectControl';
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss';


const AllComponents = () => {
  const optionsList = [
    {
      key: 'first_option',
      name: {
        title: 'First Option Title',
        subTitle: '%code_text%',
        description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint qui amet corporis ipsum? Nam, adipisci iste!'
      }
    },
    {
      key: 'second_option',
      name: {
        title: 'Second Option Title',
        subTitle: '%code_text%',
        description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. A magnam, nulla optio doloremque non, debitis aliquid dolores ad, nobis natus porro fugit sint qui amet corporis ipsum? Nam, adipisci iste!'
      }
    },
  ]

  const [value, setValue] = useState({
    key: optionsList[0].key,
    name: optionsList[0].name.title,
  });

  return (
    <div className='components-container'>
      <div>
        <SearchSelectControl
          label="Search Options"
          value={value}
          options={optionsList}
          onChange={
            ({ selectedItem: { key, name } }) => setValue({ key, name: name.props.title })
          }
        // disabled
        />
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
