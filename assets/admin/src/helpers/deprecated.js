/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { Button, Dashicon } from '@wordpress/components'

export function IconButton( props ) {
	const buttonClasses = classnames( props.className, 'has-icon' )
	const newProps = {
		...props,
		icon: false,
	}

	return (
		<Button { ...newProps } className={ buttonClasses }>
			<Dashicon icon={ props.icon } />
		</Button>
	)
}
