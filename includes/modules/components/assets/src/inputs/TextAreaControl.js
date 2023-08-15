/**
 * External dependencies
 */
import classNames from 'classnames';

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
  className,
  placeholder,
  disabled,
  ...rest
}) {

  // const getTextAreaControlClasses = () => {
  //   return classNames(
  //     className,
  //     {
  //       'is-success': isSuccess && !isError,
  //       'is-error': isError && !isSuccess,
  //       'hide-default-number-controls': type === 'number'
  //     }
  //   );
  // };

  const textControlProps = {
    className,
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