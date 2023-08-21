/**
 * Internal dependencies
 */
import '../../../scss/SingleSectionTabPanel.scss';

/**
 * WordPress dependencies
*/
import { MenuGroup, MenuItem } from '@wordpress/components';

export default function ({
  menuItems = [],
  hideSeparator = false,
  label = '',
  children,
  className,
  ...rest
}) {
  const menuGroupProps = {
    hideSeparator,
    label,
    children,
    ...rest
  }

  return (
    <MenuGroup className={`single-section-tab-panel ${className}`} {...menuGroupProps}>
      {menuItems.map(({ icon, title }) => (
        <MenuItem>
          {icon && <i className={`${icon} components-menu-item__icon`}></i>}

          <span className='components-menu-item__text'>{title}</span>
        </MenuItem>
      ))}
    </MenuGroup>
  )
};