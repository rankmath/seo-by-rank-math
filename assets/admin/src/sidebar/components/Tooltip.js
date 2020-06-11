/**
 * WordPress dependencies
 */
import { withInstanceId } from '@wordpress/compose'

const Tooltip = ( { instanceId, children } ) => {
	const id = 'rank-math-tooltip-' + instanceId
	return (
		<span className="rank-math-tooltip">
			<input id={ id } type="checkbox" />
			<label htmlFor={ id } className="dashicons-before dashicons-editor-help"></label>
			<div className="rank-math-tooltip-content">{ children }</div>
		</span>
	)
}

export default withInstanceId( Tooltip )
