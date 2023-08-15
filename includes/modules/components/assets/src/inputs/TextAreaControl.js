/**
 * Internal dependencies
 */
import '../../scss/TextAreaControl.scss';

/**
 * WordPress dependencies
 */
import { TextareaControl } from '@wordpress/components';

export default function ({
  onChange,
  value,
  rows,
  label,
  help,
  hideLabelFromVision,
  placeholder,
  disabled,
  ...rest
}) {
  const textControlProps = {
    onChange,
    value,
    help,
    hideLabelFromVision,
    label,
    placeholder,
    disabled,
    rows,
    ...rest
  }

  return (
    <TextareaControl
      {...textControlProps}
    />
  );
};