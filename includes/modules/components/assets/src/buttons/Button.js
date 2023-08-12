/**
 * External dependencies
 */

/**
 * Internal dependencies
 */
import '../../scss/Button.scss'

/**
 * WordPress dependencies
 */
import { Button as WPButton } from '@wordpress/components';

const Button = ({
  children,
  variant = 'primary',
  size = 'default',
  disabled = false,
  describedBy = '',
  focus = false,
  isBusy = false,
  isDestructive = false,
  isPressed = false,
  label = '',
  showTooltip = false,
  shortcut,
  tooltipPosition,
  href,
  target,
  text,
  icon,
  iconPosition,
  iconSize,
}) => {
  const variantClassMap = {
    'primary-outline': 'primary-outline',
    'secondary-grey': 'secondary-grey',
    tertiary: 'secondary'
  };

  const getButtonClasses = () => {
    let classes = '';

    size === 'large' ? classes += ' is-large' : '';


    if (variantClassMap[variant]) {
      classes += ` ${variantClassMap[variant]}`;
    }

    return classes;
  };

  return (
    <WPButton
      variant={variantClassMap[variant] ? 'secondary' : variant}
      aria-disabled={disabled}
      className={getButtonClasses()}
      {...{
        size,
        disabled,
        describedBy,
        focus,
        isBusy,
        isDestructive,
        isPressed,
        label,
        showTooltip,
        shortcut,
        href,
        target,
        text,
        tooltipPosition,
        icon,
        iconPosition,
        iconSize,
        children
      }}
    >
      {children}
    </WPButton>
  )
}

export default Button


export const ButtonShowcase = () => {
  return (
    <div>
      <h4>Text Buttons</h4>

      <div>
        <h3>Primary</h3>

        <div className='group-button'>
          <Button size='small'>Label</Button>
          <Button>Label</Button>
          <Button size='large' children={'Label'}></Button>

          <Button size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Primary Outline</h3>

        <div className='group-button'>
          <Button variant='primary-outline' size='small'>Label</Button>
          <Button variant='primary-outline'>Label</Button>
          <Button variant='primary-outline' size='large'>Label</Button>

          <Button variant='primary-outline' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Secondary</h3>

        <div className='group-button'>
          <Button variant='secondary' size='small'>Label</Button>
          <Button variant='secondary'>Label</Button>
          <Button variant='secondary' size='large'>Label</Button>

          <Button variant='secondary' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Secondary Grey</h3>

        <div className='group-button'>
          <Button variant='secondary-grey' size='small'>Label</Button>
          <Button variant='secondary-grey'>Label</Button>
          <Button variant='secondary-grey' size='large'>Label</Button>

          <Button variant='secondary-grey' size='large' disabled>Label</Button>
        </div>
      </div>

      <div>
        <h3>Tertiary</h3>

        <div className='group-button'>
          <Button variant='tertiary' size='small'>Label</Button>
          <Button variant='tertiary'>Label</Button>
          <Button variant='tertiary' size='large'>Label</Button>

          <Button variant='tertiary' size='large' disabled>Label</Button>
        </div>
      </div>
    </div>
  );
}

// icon={<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M10 17.389H8.444A5.194 5.194 0 1 1 8.444 7H10v1.5H8.444a3.694 3.694 0 0 0 0 7.389H10v1.5ZM14 7h1.556a5.194 5.194 0 0 1 0 10.39H14v-1.5h1.556a3.694 3.694 0 0 0 0-7.39H14V7Zm-4.5 6h5v-1.5h-5V13Z"></path></svg>}