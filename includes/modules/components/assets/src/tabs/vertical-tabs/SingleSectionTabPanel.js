/**
 * WordPress dependencies
*/
import { MenuGroup, MenuItem } from '@wordpress/components';

export default ({
	menuItems = [],
	label,
	className,
	children,
	...rest
}) => {
	const menuGroupProps = {
		className: `single-section-tab-panel ${className}`,
		label,
		children,
		...rest
	}

	return (
		<MenuGroup {...menuGroupProps}>
			{menuItems.map(({ icon, title }) => (
				<MenuItem key={title}>
					{icon && <i className={`${icon} components-menu-item__icon`}></i>}

					<span className='components-menu-item__text'>{title}</span>
				</MenuItem>
			))}
		</MenuGroup>
	)
};