/**
 * External dependencies
 */
import { map, includes, filter, every, find } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Button from '../buttons/Button'
import CheckboxControl from './CheckboxControl'
import './scss/CheckboxList.scss'

/**
 * Checkbox List component
 *
 * @param {Object}           props           Component props
 * @param {Array}            props.value     The selected options
 * @param {Array}            props.options   Checkbox options
 * @param {Function}         props.onChange  Callback executed when a checkbox is changed
 * @param {boolean | Object} props.toggleAll Set to `true` to show toggle all button or provide an object with properties to customize the button.
 * @param {string}           props.variant   Specifies the checkbox variant
 */
export default ( {
	value = [],
	options,
	onChange,
	toggleAll,
	variant = 'metabox',
} ) => {
	// Update the selected options based on the provided option id.
	const updateSelectedOptions = ( optionId ) => {
		const option = find( options, ( { id } ) => id === optionId )

		// If the option is disabled, do nothing
		if ( option.disabled ) {
			return
		}

		const updatedSelection = includes( value, optionId )
			? filter( value, ( item ) => item !== optionId ) // Remove option if already selected
			: [ ...value, optionId ] // Add option if not already selected

		onChange( updatedSelection )
	}

	// Toggle the selection state of all options.
	const toggleAllOptions = () => {
		const enabledOptions = filter( options, ( option ) => ! option.disabled )
		const enabledOptionIds = map( enabledOptions, ( option ) => option.id )
		const disabledOptionIds = map( filter( options, ( option ) => option.disabled ), ( option ) => option.id )

		// Check if all enabled options are currently selected
		const allEnabledSelected = every( enabledOptionIds, ( id ) => includes( value, id ) )

		const newSelection = allEnabledSelected
			? filter( value, ( id ) => includes( disabledOptionIds, id ) ) // Deselect all enabled options if all are selected
			: [ ...value, ...filter( enabledOptionIds, ( id ) => ! includes( value, id ) ) ] // Select all enabled options if not all are selected

		onChange( newSelection )
	}

	return (
		<>
			{ toggleAll && (
				<Button
					onClick={ toggleAllOptions }
					className="field-multicheck-toggle"
					children={ __( 'Select / Deselect All', 'rank-math' ) }
					{ ...toggleAll }
				/>
			) }

			<ul className="rank-math-checkbox-list">
				{ map( options, ( { id, label, ...additionalProps } ) => (
					<li key={ id }>
						<CheckboxControl
							{ ...additionalProps }
							variant={ variant }
							checked={ includes( value, id ) }
							onChange={ () => updateSelectedOptions( id ) }
							label={ (
								<RawHTML>
									{ label }
								</RawHTML>
							) }
						/>
					</li>
				) ) }
			</ul>
		</>
	)
}
