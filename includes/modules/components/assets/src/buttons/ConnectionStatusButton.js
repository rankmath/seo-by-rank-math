/**
 * Internal dependencies
 */
import '../../scss/connection-status-button.scss'

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default function ({
  status = 'connected',
  iconName = 'rm-icon-cross',
  className,
  children,
  ...rest
}) {
  const groupedClassNames = `connection-status-button ${className}`;
  // const iconName = 'rm-icon-cross';

  const buttonProps = {
    ...rest,
    children,
    className: groupedClassNames,
    icon: <i className={iconName}></i>,
    variant: 'secondary',
  }

  return (
    <Button
      {...buttonProps}
    />
  )
};
