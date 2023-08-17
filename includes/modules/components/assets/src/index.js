/**
 * WordPress dependencies
 */
import { createElement, render } from '@wordpress/element';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import ToggleControl from './controls/ToggleControl';
import CheckboxControl from './controls/CheckboxControl';
import RadioControl from './controls/RadioControl';
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss'


const AllComponents = () => {
  const [value, setValue] = useState(false);
  const [option, setOption] = useState('a');
  const [isChecked, setChecked] = useState(true);

  const initialCheckboxes = [
    { id: 'checkbox-1', label: 'Checkbox 1', checked: false },
    { id: 'checkbox-2', label: 'Checkbox 2', checked: false },
    { id: 'checkbox-3', label: 'Checkbox 3', checked: false },
  ];

  const [checkboxes, setCheckboxes] = useState(initialCheckboxes);

  const handleCheckboxChange = (id) => {
    const updatedCheckboxes = checkboxes.map((checkbox) =>
      checkbox.id === id ? { ...checkbox, checked: !checkbox.checked } : checkbox
    );
    setCheckboxes(updatedCheckboxes);
  };

  const areAllChecked = checkboxes.every((checkbox) => checkbox.checked);
  const areAnyChecked = checkboxes.some((checkbox) => checkbox.checked);

  const handleMasterCheckboxChange = () => {
    const newCheckValue = !areAllChecked;
    const updatedCheckboxes = checkboxes.map((checkbox) => ({
      ...checkbox,
      checked: newCheckValue,
    }));
    setCheckboxes(updatedCheckboxes);
  };

  return (
    <div className='components-container'>
      <div className='group-components'>
        <CheckboxControl
          label="Select All"
          checked={areAllChecked}
          indeterminate={!areAllChecked && areAnyChecked}
          isIndeterminate
          onChange={handleMasterCheckboxChange}
        // disabled
        />
        {checkboxes.map((checkbox) => (
          <CheckboxControl
            key={checkbox.id}
            label={checkbox.label}
            checked={checkbox.checked}
            onChange={() => handleCheckboxChange(checkbox.id)}
          // disabled
          />
        ))}
      </div>

      <div className="group-components">
        <CheckboxControl
          label="Checkbox"
          checked={isChecked}
          onChange={setChecked}
        // disabled
        />
      </div>

      <div className='group-components'>
        <RadioControl
          label="Radio"
          selected={option}
          options={[
            { label: 'Selected', value: 'a' },
            { label: 'Default', value: 'e' },
          ]}
          onChange={(value) => setOption(value)}
        // disabled
        />
      </div>

      <div className='group-components'>
        <ToggleControl
          help='Toggle'
          label="On"
          checked={value}
          onChange={() => setValue((state) => !state)}
        />

        <ToggleControl
          label="Off"
          help='Toggle'
          checked={value}
          onChange={() => setValue((state) => !state)}
          disabled
        />
      </div>
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
