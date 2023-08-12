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
