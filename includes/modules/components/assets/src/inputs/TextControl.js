
/**
 * Internal dependencies
 */
import '../../scss/TextControl.scss';

/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components';

export default function ({
  type,
  placeholder,
  onChange,
  value,
  help,
  hideLabelFromVision,
  label,
  className = '',
  disabled,
  isSuccess,
  isError,
}) {
  const getTextControlClasses = () => {
    let classes = '';

    isSuccess && !isError ? classes += ' is-success' : '';
    isError && !isSuccess ? classes += ' is-error' : ''

    const finalClass = classes += ` ${className}`

    return finalClass;
  };

  const textControlProps = {
    className: getTextControlClasses(),
    type,
    onChange,
    value,
    help,
    hideLabelFromVision,
    label,
    placeholder,
    disabled,
  }

  return (
    <div className='text-control-container'>
      <TextControl
        {...textControlProps}
      />

      {(isSuccess && !isError) &&
        <div className='text-control-icon'>
          <i className="rm-icon-tick validation-icon is-success"></i>
        </div>
      }

      {(isError && !isSuccess) &&
        <div className='text-control-icon'>
          <i className="rm-icon-trash validation-icon is-error"></i>
        </div>
      }
    </div>
  )
}