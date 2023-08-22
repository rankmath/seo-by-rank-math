/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/checkbox-control.scss';

/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';

export default function ({
  __nextHasNoMarginBottom = false,
  onChange = () => { },
  indeterminate = false,
  isIndeterminate = false,
  label,
  help,
  checked,
  className,
  disabled,
  ...rest
}) {
  const getCheckboxControlClasses = () => {
    return classNames(
      className,
      {
        'is-disabled': disabled,
        'is-indeterminate': isIndeterminate,
      }
    );
  };

  const checkboxControlProps = {
    className: getCheckboxControlClasses(),
    __nextHasNoMarginBottom,
    onChange,
    indeterminate,
    label,
    help,
    checked,
    disabled,
    ...rest
  }

  return (
    <CheckboxControl
      {...checkboxControlProps}
    />
  );
};