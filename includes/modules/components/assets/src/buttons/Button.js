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

const Button = ({ children, variant = 'primary', size = 'default', disabled = false }) => {
  const getButtonClasses = () => {
    const classes = []

    size === 'large' && classes.push('is-large');

    variant === 'primary-outline' && classes.push('primary-outline');
    variant === 'secondary-grey' && classes.push('secondary-grey');

    return classes.join(' ')
  }

  return (
    <WPButton
      variant={(variant === 'primary-outline' || variant === 'secondary-grey' || variant === 'tertiary') ? 'secondary' : variant}
      size={size}
      disabled={disabled}
      aria-disabled={disabled}
      className={getButtonClasses()}
    >
      {children}
    </WPButton>
  )
}


export default Button
