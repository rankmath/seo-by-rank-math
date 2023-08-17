/**
 * Internal dependencies
 */
import '../../scss/RadioControl.scss';

/**
 * WordPress dependencies
 */
import { RadioControl } from '@wordpress/components';

export default function ({
  hideLabelFromVision = false,
  onChange = () => { },
  label,
  help,
  selected,
  options,
  ...rest
}) {
  const radioControlProps = {
    hideLabelFromVision,
    onChange,
    label,
    help,
    selected,
    options,
    ...rest,
  }

  return (
    <RadioControl
      {...radioControlProps}
    />
  );
};