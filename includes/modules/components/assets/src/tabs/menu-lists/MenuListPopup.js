/**
 * WordPress dependencies
*/
import { MenuGroup, MenuItem } from '@wordpress/components';

export default ({
	menuItems = [],
	className,
	children,
	...rest
}) => {
	const groupedClassNames = `menu-list-popup ${className}`;

	const menuGroupProps = {
		...rest,
		className: groupedClassNames,
		children
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
