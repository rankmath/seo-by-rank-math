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
    className,
    ...rest
  }

  return (
    <MenuGroup {...menuGroupProps}>
      {menuItems.map(({ icon, title }) => (
        <MenuItem>
          {icon && <i className={`${icon} components-menu-item__icon`}></i>}

          <span>{title}</span>
        </MenuItem>
      ))}
    </MenuGroup>
  )
};