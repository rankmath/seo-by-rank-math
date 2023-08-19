/**
 * External dependencies
 */
import classNames from 'classnames';

/**
 * Internal dependencies
 */
import '../../scss/Notice.scss';

/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';

export default function ({
  status = 'info',
  actions = [],
  isDismissible = true,
  politeness = 'polite',
  children,
  className,
  spokenMessage,
  onRemove,
  icon,
  ...rest
}) {
  const statusToTitle = {
    error: 'Error!',
    success: 'Success!',
    warning: 'Warning!',
  };

  const title = statusToTitle[status] || 'Notice!';

  const getNoticeClasses = () => {
    return classNames(
      {
        'has-icon': icon,
      }
    );
  };

  const noticeProps = {
    className: getNoticeClasses(),
    status,
    actions,
    isDismissible,
    politeness,
    spokenMessage,
    onRemove,
    ...rest
  }

  return (
    <Notice {...noticeProps} >
      {icon &&
        <div className='components-notice__icon'>
          <i className={icon}></i>
        </div>
      }

      <div className='components-notice__text'>
        {!icon &&
          <b className='components-notice__title'>{title}</b>
        }

        <>
          {children}
        </>
      </div>
    </Notice>
  )
}
