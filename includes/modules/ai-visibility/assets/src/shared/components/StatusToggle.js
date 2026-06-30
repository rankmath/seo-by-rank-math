/**
 * StatusToggle — enabled/disabled checkbox for a query row.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { CheckboxControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './StatusToggle.scss'

/**
 * StatusToggle component.
 *
 * @param {Object}   props
 * @param {boolean}  props.value             Checked state — true = enabled, false = disabled.
 * @param {Function} props.onChange          Called with the new boolean value on change.
 * @param {boolean}  [props.disabled=false]  Disables interaction when true.
 * @param {boolean}  [props.isLoading=false] Shows in-progress label when true.
 * @return {JSX.Element} Checkbox toggle with contextual label.
 */
const StatusToggle = ( { value, onChange, disabled = false, isLoading = false } ) => {
	let label
	if ( isLoading ) {
		label = value
			? __( 'Enabling…', 'seo-by-rank-math' )
			: __( 'Disabling…', 'seo-by-rank-math' )
	} else {
		label = value
			? __( 'Enabled', 'seo-by-rank-math' )
			: __( 'Disabled', 'seo-by-rank-math' )
	}

	return (
		<CheckboxControl
			className="rank-math-ai-visibility-status-toggle"
			label={ label }
			checked={ value }
			onChange={ onChange }
			disabled={ disabled || isLoading }
			__nextHasNoMarginBottom={ true }
		/>
	)
}

StatusToggle.displayName = 'StatusToggle'

export default StatusToggle
