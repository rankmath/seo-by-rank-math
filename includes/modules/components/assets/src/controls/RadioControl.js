/**
 * Internal dependencies
 */
import '../../scss/radio-control.scss';

/**
 * WordPress dependencies
 */
import { RadioControl } from '@wordpress/components';

export default ({
  hideLabelFromVision = false,
  onChange = () => { },
  label,
  help,
  selected,
  options,
  ...rest
}) => {
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