/**
 * External dependencies.
 */
import classNames from 'classnames'

const AdminNotice = ( {
	type = 'info', // one of: success, info, warning, error
	isDismissible = false,
	children,
	...rest
} ) => {
	const className = classNames(
		'notice',
		'notice-' + type,
		{ 'is-dismissible': isDismissible }
	)
	return (
		<div className={ className } { ...rest }>
			{ children }
		</div>
	)
}

export default AdminNotice
