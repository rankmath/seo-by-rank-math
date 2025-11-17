/**
 * Internal Dependencies
 */
import getStatusIcons from './getStatusIcons'
import getStatusLabels from './getStatusLabels'

/**
 * Output test result status.
 *
 * @param {string} status
 */
export default ( status = 'info' ) => {
	return (
		<div
			className={ `status-icon ${ getStatusIcons( status ) }` }
			title={ getStatusLabels( status ) }
		/>
	)
}
