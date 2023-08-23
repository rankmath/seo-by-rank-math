/**
 * Internal dependencies
 */
import '../../scss/connection-button.scss'

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default function ({
  status = 'connect',
  className,
  children,
  ...rest
}) {
  const statusIconMap = {
    connect: 'rm-icon-plus',
    connected: 'rm-icon-tick',
    disconnect: 'rm-icon-cross',
    disconnected: 'rm-icon-cross',
    reconnect: 'rm-icon-trash',
  };
  const iconName = statusIconMap[status] || '';
  const groupedClassNames = `connection-button ${status} ${className}`;

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
