/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { withInstanceId } from '@wordpress/compose'

const Tooltip = ( { className, instanceId, children } ) => {
	const id = 'rank-math-tooltip-' + instanceId
	const classes = classnames( 'rank-math-tooltip', className )

	return (
		<span className={ classes }>
			<input id={ id } type="checkbox" />
			<label htmlFor={ id } className="dashicons-before dashicons-editor-help"></label>
			<div className="rank-math-tooltip-content">{ children }</div>
		</span>
	)
}

export default withInstanceId( Tooltip )
