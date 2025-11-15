/**
 * External dependencies
 */
import { isNull } from 'lodash'

/**
 * Checks if the data API from Site Editor is available.
 *
 * @return {boolean} True if the data API is available.
 */
export default () => {
	return ! isNull( document.getElementById( 'site-editor' ) )
}
