/**
 * External dependencies
 */
import classNames from 'classnames'

const ModalButton = ( {
	className,
	children,
	...rest
} ) => {
	const classes = classNames(
		'rank-math-rm-modal-button',
		className
	)
	return (
		<button
			type="button"
			className={ classes }
			{ ...rest }
		>
			{ children }
		</button>
	)
}

export default ModalButton
