/**
 * WordPress dependencies
 */
import { createElement, render } from '@wordpress/element';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
*/
import Button from './buttons/Button';
import TextControl from './inputs/TextControl';
import TextAreaControl from './inputs/TextAreaControl';
import CustomSelectControl from './select/CustomSelectControl';
import ToggleControl from './controls/ToggleControl';
import RadioControl from './controls/RadioControl';
import '../../../../../assets/admin/scss/_font-icons.scss';
import '../scss/components.scss'


const AllComponents = () => {
  const [size, setSize] = useState({
    key: "large",
    name: "Large",
  });

  const options = [
    {
      key: "thumbnail",
      name: "Thumbnail",
    },
    {
      key: "medium",
      name: "Medium",
    },
    {
      key: "large",
      name: "Large",
    },
    {
      key: "full",
      name: "Full Size",
    }
  ]
  const [value, setValue] = useState(false);

  const [option, setOption] = useState('a');

  return (
    <div className='components-container'>
      <div className='group-components'>
        <RadioControl
          label="User type"
          selected={option}
          options={[
            { label: 'Selected', value: 'a' },
            { label: 'Default', value: 'e' },
          ]}
          onChange={(value) => setOption(value)}
          disabled
        />
      </div>
    </div>
  );
};

function ToggleControlShowcase() {
  return (
    <>
      <div className='group-components'>
        <ToggleControl
          help='Help me'
          label="On"
          checked={value}
          onChange={() => setValue((state) => !state)}
        />
      </div>

      <div className='group-components'>
        <ToggleControl
          label="Off"
          checked={value}
          onChange={() => setValue((state) => !state)}
          disabled
        />
      </div>
    </>
  )
}

function CustomSelectControlShowcase() {
  return (
    <>
      <div className='group-components'>
        <CustomSelectControl
          label="Label"
          value={size}
          options={options}
          onChange={(target) => setSize(target.selectedItem)}
        />
        <CustomSelectControl
          label="Label"
          value={size}
          options={options}
          onChange={(target) => setSize(target.selectedItem)}
          disabled
        />
      </div>

      <div className='group-components'>
        <CustomSelectControl
          value={size}
          options={options}
          onChange={(target) => setSize(target.selectedItem)}
        />
        <CustomSelectControl
          value={size}
          options={options}
          onChange={(target) => setSize(target.selectedItem)}
          disabled
        />
      </div>
    </>
  )
}

function TextAreaControlShowcase() {
  return (
    <>
      <div className='group-components'>
        <TextAreaControl placeholder='Placeholder Text' />
        <TextAreaControl placeholder='Disabled Field' disabled />
      </div>
    </>
  )
}

function TextControlShowcase() {
  return (
    <>
      <div className='group-components'>
        <TextControl type='number' placeholder='Placeholder Text' />
        <TextControl placeholder='Placeholder Text' />
        <TextControl placeholder='Disabled Field' disabled />
      </div>

      <div className='group-components'>
        <TextControl isSuccess />
        <TextControl value='email@website.com' isError />
      </div>
    </>
  )
}

function ButtonsShowcase() {
  return (
    <div>
      <h4>Text Buttons</h4>

      <div>
        <h3>Primary</h3>

        <div className='group-components'>
          <Button size='small' onMouseOver={() => console.log('clicked')}>Label</Button>

          <Button icon={<i className='rm-icon-trash'></i>}>Label</Button>

          <Button size='large' children={'Label'} />

          <Button size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Primary Outline</h3>

        <div className='group-components'>
          <Button variant='primary-outline' size='small'>Label</Button>

          <Button variant='primary-outline' icon={<i className='rm-icon-trash'></i>}>Label</Button>

          <Button variant='primary-outline' size='large'>Label</Button>

          <Button variant='primary-outline' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Secondary</h3>

        <div className='group-components'>
          <Button variant='secondary' size='small'>Label</Button>

          <Button variant='secondary' icon={<i className='rm-icon-trash'></i>}>Label</Button>

          <Button variant='secondary' size='large'>Label</Button>

          <Button variant='secondary' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Secondary Grey</h3>

        <div className='group-components'>
          <Button variant='secondary-grey' size='small'>Label</Button>

          <Button variant='secondary-grey' icon={<i className='rm-icon-trash'></i>}>Label</Button>

          <Button variant='secondary-grey' size='large'>Label</Button>

          <Button variant='secondary-grey' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Tertiary Outline</h3>

        <div className='group-components'>
          <Button variant='tertiary-outline' size='small'>Label</Button>

          <Button variant='tertiary-outline' icon={<i className='rm-icon-trash'></i>}>Label</Button>

          <Button variant='tertiary-outline' size='large'>Label</Button>

          <Button variant='tertiary-outline' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Tertiary</h3>

        <div className='group-components'>
          <Button variant='tertiary' size='small'>Label</Button>

          <Button variant='tertiary' icon={<i className='rm-icon-trash'></i>}>Label</Button>

          <Button variant='tertiary' size='large'>Label</Button>

          <Button variant='tertiary' size='large' disabled>Label</Button>
        </div>
      </div>
    </div>
  );
}

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
