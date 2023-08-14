/**
 * Internal dependencies
 */
import '../../scss/Button.scss'

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default function ({
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
}) {
  const variantClassMap = {
    'primary-outline': 'primary-outline',
    'secondary-grey': 'secondary-grey',
    'tertiary-outline': 'secondary',
  };

  const getButtonClasses = () => {
    let classes = '';

    size === 'large' ? classes += ' is-large' : '';


    if (variantClassMap[variant]) {
      classes += ` ${variantClassMap[variant]}`;
    } else if (variant === 'tertiary') {
      classes += 'tertiary'
    }

    return classes;
  };

  return (
    <Button
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
    </Button>
  )
}


// icon={<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M10 17.389H8.444A5.194 5.194 0 1 1 8.444 7H10v1.5H8.444a3.694 3.694 0 0 0 0 7.389H10v1.5ZM14 7h1.556a5.194 5.194 0 0 1 0 10.39H14v-1.5h1.556a3.694 3.694 0 0 0 0-7.39H14V7Zm-4.5 6h5v-1.5h-5V13Z"></path></svg>}