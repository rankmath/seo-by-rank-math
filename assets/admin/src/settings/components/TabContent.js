/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * Internal dependenies
 */
import TabHeader from './TabHeader'
import TabFooter from './TabFooter'
import TabFields from './TabFields'

/**
 * Settings content.
 *
 * @param {Object} props          Component props.
 * @param {string} props.type     The setting type.
 * @param {Object} props.header   The tab header props. Accepted values are: 'title', 'description' and 'link'.
 * @param {Object} props.footer   The tab footer's buttons props. Accepted values are: 'discardButton' and 'applyButton'.
 * @param {Array}  props.fields   Array of form fields.
 * @param {Object} props.settings Settings data.
 * @param {Object} props.tabs     Current settings tabs.
 */
export default ( { type, header, footer, fields = [], settings = null, tabs } ) => {
	if ( isEmpty( settings ) ) {
		return null
	}

	return (
		<>
			{ header && <TabHeader { ...header } /> }
			<TabFields settingType={ type } fields={ fields } settings={ settings } />
			{ footer && <TabFooter type={ type } footer={ footer } tabs={ tabs } /> }
		</>
	)
}
