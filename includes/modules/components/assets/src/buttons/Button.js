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
    'primary-outline': 'is-primary-outline',
    'secondary-grey': 'is-secondary-grey',
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