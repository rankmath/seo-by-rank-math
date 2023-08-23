/**
 * Internal dependencies
 */
import '../../scss/anchor-tag-status-button.scss'

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export default function ({
  severity = 'good',
  href = '',
  target = '_blank',
  rel = 'noopener noreferrer',
  className,
  children,
  ...rest
}) {
  const groupedClassNames = `anchor-tag-status-button ${className} ${severity}`;

  const buttonProps = {
    ...rest,
    href,
    children,
    target,
    rel,
    className: groupedClassNames,
    variant: 'secondary',
  }

  return (
    <Button
      {...buttonProps}
    />
  )
};
