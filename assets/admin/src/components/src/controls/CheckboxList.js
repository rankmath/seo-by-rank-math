/**
 * External dependencies
 */
import { map, includes, filter, every, find } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import Button from '../buttons/Button'
import CheckboxControl from './CheckboxControl'
import './scss/CheckboxList.scss'

/**
 * Checkbox List component
 *
 * @param {Object}   props           Component props
 * @param {Array}    props.options   Checkbox options
 * @param {Function} props.onChange  Callback executed when a checkbox is changed
 * @param {*}        props.toggleAll Set to `true` to show toggle all button or provide an object with properties to customize the toggle all button.
 * @param {Array}    props.selected  The selected options
 */
export default ( {
	options,
	onChange,
	toggleAll,
	selected = [],
} ) => {
	// Update the selected options based on the provided option id.
	const updateSelectedOptions = ( optionId ) => {
		const option = find( options, ( { id } ) => id === optionId )

		// If the option is disabled, do nothing
		if ( option.disabled ) {
			return
		}

		const updatedSelection = includes( selected, optionId )
			? filter( selected, ( item ) => item !== optionId ) // Remove option if already selected
			: [ ...selected, optionId ] // Add option if not already selected

		onChange( updatedSelection )
	}

	// Toggle the selection state of all options.
	const toggleAllOptions = () => {
		const enabledOptions = filter( options, ( option ) => ! option.disabled )
		const enabledOptionIds = map( enabledOptions, ( option ) => option.id )
		const disabledOptionIds = map( filter( options, ( option ) => option.disabled ), ( option ) => option.id )

		// Check if all enabled options are currently selected
		const allEnabledSelected = every( enabledOptionIds, ( id ) => includes( selected, id ) )

		const newSelection = allEnabledSelected
			? filter( selected, ( id ) => includes( disabledOptionIds, id ) ) // Deselect all enabled options if all are selected
			: [ ...selected, ...filter( enabledOptionIds, ( id ) => ! includes( selected, id ) ) ] // Select all enabled options if not all are selected

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
							variant="metabox"
							checked={ includes( selected, id ) }
							onChange={ () => updateSelectedOptions( id ) }
							label={ <span dangerouslySetInnerHTML={ { __html: label } } /> }
						/>
					</li>
				) ) }
			</ul>
		</>
	)
}
