/**
 * WordPress dependencies
 */
import {
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	TextControl,
} from '@wordpress/components'

export default ( { value, onChange } ) => {
	return (
		<ToggleGroupControlOption
			value={ value }
			label={ <TextControl value={ value } onChange={ onChange } /> }
			className="rank-math-custom-toggle-group-control-option"
		/>
	)
}
