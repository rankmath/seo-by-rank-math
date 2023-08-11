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

const Button = ({ children, variant = 'primary', size, disabled = false }) => {
  let buttonClass = ''

  if (size === 'large') {
    buttonClass += ' is-large'
  }

  return (
    <WPButton
      variant={variant}
      size={size}
      disabled={disabled}
      aria-disabled={disabled}
      className={buttonClass}
    >
      {children}
    </WPButton>
  )
}


export default Button
