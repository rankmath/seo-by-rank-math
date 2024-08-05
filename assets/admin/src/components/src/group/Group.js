/**
 * External dependencies
 */
import { map, filter } from 'lodash'

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Field from '@rank-math-settings/fields'
import Button from '../buttons/Button'

/**
 * Group component.
 *
 * @param {Object} props         The component props.
 * @param {Array}  props.fields  Array of form field objects.
 * @param {Array}  props.options Array of button properties. Accepted values are: 'addButton' and 'removeButton'.
 */
export default ( { fields, options } ) => {
	const [ groupField, setGroupField ] = useState( [ fields ] )
	const { addButton, removeButton } = options

	// Handler for adding a new group of fields.
	const handleAdd = () => {
		setGroupField( ( prevState ) => [ ...prevState, fields ] )
	}

	// Handler for removing a group of fields.
	const handleRemove = ( index ) => {
		if ( groupField.length > 1 ) {
			const filterGroupField = filter( groupField, ( _, i ) => i !== index )
			setGroupField( filterGroupField )
		}
	}

	return (
		<>
			{ map( groupField, ( group, groupIndex ) => (
				<div key={ groupIndex } className="field-repeatable-grouping">
					{ map( group, ( field, fieldIndex ) => (
						<div
							key={ fieldIndex }
							className={ `group-item group-item-${ field.type }` }
						>
							<Field field={ field } />
						</div>
					) ) }

					{ removeButton && (
						<Button
							{ ...removeButton }
							variant="remove-group"
							onClick={ () => handleRemove( groupIndex ) }
						/>
					) }
				</div>
			) ) }

			{ addButton && <Button onClick={ handleAdd } { ...addButton } /> }
		</>
	)
}
