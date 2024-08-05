/**
 * Internal dependenies
 */
import TabHeader from './TabHeader'
import TabFooter from './TabFooter'
import TabFields from './TabFields'
import { getStore } from '../redux/store'

// Initialize store
getStore()

/**
 * Settings content.
 *
 * @param {Object} props        Component props.
 * @param {string} props.type   The setting type.
 * @param {Array}  props.fields Array of form fields.
 * @param {Object} props.header The tab header props. Accepted values are: 'title', 'description' and 'link'.
 * @param {Object} props.footer The tab footer's buttons props. Accepted values are: 'discardButton' and 'applyButton'.
 */
export default ( { type, header, footer, fields = [] } ) => {
	return (
		<>
			{ header && <TabHeader { ...header } /> }
			<TabFields settingType={ type } fields={ fields } />
			{ footer && <TabFooter type={ type } footer={ footer } /> }
		</>
	)
}
