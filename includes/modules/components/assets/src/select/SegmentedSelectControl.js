/**
 * Internal dependencies
 */
import '../../scss/segmented-select-control.scss'

/**
 * WordPress dependencies
 */
import { MenuGroup, MenuItem } from '@wordpress/components';

export default ({ menuItems }) => {
  return (
    <MenuGroup>
      {menuItems.map(item => (
        <MenuItem key={item}>{item}</MenuItem>
      ))
      }
    </MenuGroup>
  );
}