/**
 * Internal dependencies
 */
import '../../scss/PageTabPanel.scss';

/**
 * WordPress dependencies
 */
import { TabPanel } from '@wordpress/components';

export default function ({
  activeClass = 'is-active',
  orientation = 'horizontal',
  selectOnMove = true,
  tabs = [],
  children = () => { },
  onSelect,
  className,
  initialTabName,
  ...rest
}) {
  const tabPanelProps = {
    activeClass,
    orientation,
    selectOnMove,
    children,
    tabs,
    onSelect,
    className,
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