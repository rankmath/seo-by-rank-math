/**
 * Internal dependencies
 */
import '../../scss/ToggleControl.scss';

/**
 * WordPress dependencies
 */
import { ToggleControl } from '@wordpress/components';


export default function ({
  onChange = () => { },
  __nextHasNoMarginBottom = true,
  label,
  checked,
  disabled,
  help,
  className,
  ...rest
}) {
  const toggleControlProps = {
    label,
    checked,
    onChange,
    disabled,
    help,
    className,
    __nextHasNoMarginBottom,
    ...rest
  }

  return (
    <ToggleControl
      {...toggleControlProps}
    />
  );
};