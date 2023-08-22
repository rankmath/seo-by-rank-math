/**
 * Internal dependencies
 */
import '../../../scss/page-tab-panel.scss';

/**
 * WordPress dependencies
 */
import { TabPanel } from '@wordpress/components';

export default function ({
  tabs = [],
  children = () => { },
  className,
  onSelect,
  initialTabName,
  ...rest
}) {
  const tabPanelProps = {
    className: `page-tab-panel__content ${className}`,
    tabs,
    children,
    onSelect,
    initialTabName,
    ...rest
  }

  return (
    <div className='page-tab-panel__container'>
      <TabPanel
        {...tabPanelProps}
      />

      <button className='page-tab-panel__button'>
        <i className='rm-icon-trash'></i>
      </button>
    </div>
  )
}