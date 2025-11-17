/**
 * External dependencies
 */
import { isNull } from 'lodash'

/**
 * Handles navigation logic for search result selection.
 *
 * @param {Object} field Selected field data including tab and setting.
 */

export default ( field ) => {
	const { setting, tab } = field
	if ( setting !== rankMath.optionPage ) {
		window.location.href = `${ window.location.origin }/wp-admin/admin.php?page=rank-math-options-${ setting }&tab=${ tab }`
		return
	}

	const element = document.getElementById( `tab-panel-0-${ tab }` )
	if ( ! isNull( element ) ) {
		element.click()
	}
}
