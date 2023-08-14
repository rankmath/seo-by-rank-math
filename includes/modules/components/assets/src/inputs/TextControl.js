/**
 * Internal dependencies
 */
import '../../scss/TextControl.scss'

/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components';
import '../../scss/TextControl.scss'

export default function ({
  type,
  placeholder,
  onChange,
  value,
  help,
  hideLabelFromVision,
  label,
  className,
  disabled,
}) {
  return (
    <TextControl
      {...{
        type,
        onChange,
        value,
        help,
        hideLabelFromVision,
        label,
        className,
        placeholder,
        disabled,
      }}
    />
  )
}