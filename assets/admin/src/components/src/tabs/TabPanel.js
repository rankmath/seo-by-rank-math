/**
 * WordPress Dependencies
 */
import { TabPanel } from '@wordpress/components'

/**
 * Internal Dependencies
 */
import './scss/TabPanel.scss'

/**
 * Tab Panel component.
 *
 * @param {Object} props           Component props.
 * @param {string} props.className CSS class for additional styling.
 */
export default ( { className = '', ...additionalProps } ) => {
	className = `rank-math-tab-panel ${ className }`

	return <TabPanel { ...{ className, ...additionalProps } } />
}
