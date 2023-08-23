/**
 * Internal dependencies
 */
import '../../scss/anchor-tag-status.scss'

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
  const groupedClassNames = `anchor-tag-status ${className} ${severity}`;

  const buttonProps = {
    ...rest,
    href,
    children,
    target,
    rel,
    className: groupedClassNames,
    variant: 'is-secondary',
  }

  return (
    <Button
      {...buttonProps}
    />
  )
};
