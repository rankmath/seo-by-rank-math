/**
 * Internal dependencies
 */
import Tooltip from './Tooltip'

/**
 * Help Icon tooltip.
 *
 * @param {Object} props      Component props.
 * @param {string} props.text Message to show in tooltip.
 */
export default ( { text } ) => (
	<Tooltip text={ text }>
		<em className="dashicons-before dashicons-editor-help" />
	</Tooltip>
)
