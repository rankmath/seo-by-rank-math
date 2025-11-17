/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * Internal dependencies
 */
import Button from '../buttons/Button'
import icons from '../icons'
import './scss/StatusButton.scss'

/**
 * Status Button component.
 *
 * @param {Object}   props           Component props.
 * @param {Node}     props.children  Child elements to be rendered inside the button.
 * @param {string}   props.className CSS class for additional styling.
 * @param {Function} props.onClick   Callback invoked when the button is clicked.
 * @param {string}   props.status    Specifies the button's style. Accepted values: 'connected' or 'disconnected'.
 */
export default ( { children, className, onClick, status, ...additionalProps } ) => {
	className = classNames( className, `is-${ status }`, 'rank-math-status-button' )

	const props = {
		...additionalProps,
		onClick,
		children,
		className,
		icon: icons.statusIcons[ status ],
		isDestructive: status === 'disconnect',
		size: status === 'disconnect' ? 'xlarge' : 'large',
	}

	return <Button { ...props } />
}
