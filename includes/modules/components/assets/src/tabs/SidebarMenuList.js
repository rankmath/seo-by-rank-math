/**
 * Internal dependencies
 */
import '../../scss/SidebarMenuList.scss';

/**
 * WordPress dependencies
*/
import { MenuGroup, MenuItem } from '@wordpress/components';

export default function ({
  menuItems = [],
  hideSeparator = false,
  children,
  ...rest
}) {
  const menuGroupProps = {
    hideSeparator,
    children,
    ...rest
  }

  return (
    <MenuGroup className='sidebar-menu-list' {...menuGroupProps}>
      {menuItems.map(({ icon, title }) => (
        <MenuItem>
          {icon && <i className={`${icon} components-menu-item__icon`}></i>}

          <span className='components-menu-item__text'>{title}</span>
        </MenuItem>
      ))}
    </MenuGroup>
  )
};
