/**
 * Internal dependencies
 */
import '../../../scss/menu-list-popup.scss';

/**
 * WordPress dependencies
*/
import { MenuGroup, MenuItem } from '@wordpress/components';

export default function ({
  menuItems = [],
  className,
  children,
  ...rest
}) {
  const menuGroupProps = {
    className: `menu-list-popup ${className}`,
    children,
    ...rest
  }

  return (
    <MenuGroup {...menuGroupProps}>
      {menuItems.map(({ icon, title }) => (
        <MenuItem key={title}>
          <i className={`${icon} components-menu-item__icon`}></i>

          <span className='components-menu-item__text'>{title}</span>
        </MenuItem>
      ))}
    </MenuGroup>
  )
};
