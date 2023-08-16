/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/CustomSelectControl.scss';

/**
 * WordPress dependencies
 */
import { CustomSelectControl } from '@wordpress/components';

export default function ({
  label,
  value,
  onChange,
  options,
  size,
  disabled,
  className,
  ...rest
}) {
  const getSelectControlClasses = () => {
    return classNames(
      className,
      {
        'is-disabled': disabled,
        'with-label': label
      }
    );
  };

  const selectControlProps = {
    className: getSelectControlClasses(),
    label,
    value,
    onChange,
    options,
    disabled,
    size,
    __next36pxDefaultSize: true,
    ...rest
  }

  return (
    <CustomSelectControl
      {...selectControlProps}
    />
  );
};