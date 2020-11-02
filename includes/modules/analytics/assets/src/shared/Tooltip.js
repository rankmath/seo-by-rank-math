/**
 * External dependencies
 */
import classnames from 'classnames'

const Tooltip = ( { className, children } ) => {
	const classes = classnames( 'rank-math-tooltip', className )
	return (
		<span className={ classes }>
			<em className="dashicons-before dashicons-editor-help" />
			<span>{ children }</span>
		</span>
	)
}

export default Tooltip
