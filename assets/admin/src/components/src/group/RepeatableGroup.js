/**
 * External dependencies
 */
import { map, filter, split, slice, isUndefined, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Button from '../buttons/Button'
import Field from '@rank-math-settings/components/Field'

import './scss/RepeatableGroup.scss'

/**
 * Group component.
 *
 * @param {Object}   props           The component props.
 * @param {string}   props.id        The unique group identifier.
 * @param {Array}    props.value     The group value.
 * @param {Function} props.onChange  Callback invoked when the checkbox selection changes.
 * @param {Array}    props.options   Array of button properties. Accepted values are: 'addButton' and 'removeButton'.
 * @param {Array}    props.fields    Array of group fields.
 * @param {Object}   props.default   The default field values.
 * @param {string}   props.className Adds custom classes to component.
 */
export default ( {
	id,
	value,
	onChange,
	options,
	fields,
	className = '',
	default: defaultValue = {},
} ) => {
	className = `field-repeatable-grouping ${ className }`
	const { addButton, removeButton } = options

	// Add a new group of fields.
	const handleAdd = () => {
		const newGroupValue = [ ...value, defaultValue ]

		onChange( newGroupValue )
	}

	// Remove a group based on the group id.
	const handleRemove = ( removeGroupId ) => {
		const newGroupValue = filter( value, ( _, index ) => index !== removeGroupId )

		onChange( newGroupValue )
	}

	// Handle field change
	const handleChange = ( newValue, groupId, field ) => {
		const newGroupValue = map( value, ( group, index ) => {
			const fieldId = field.id
			if ( groupId === index ) {
				if ( field.type === 'file' ) {
					group = isEmpty( group ) ? {} : group
					group[ fieldId ] = newValue.url
					group[ fieldId + '_id' ] = newValue.id
					return group
				}
				return { ...group, [ fieldId ]: newValue }
			}

			return group
		} )

		onChange( newGroupValue )
	}

	// Handle when the text field receives a pasted content
	const handlePaste = ( event, groupId, fieldId ) => {
		const valueAlreadyExists = value[ groupId ][ fieldId ]
		if ( valueAlreadyExists ) {
			return
		}

		event.preventDefault()

		const clipboardData = event.clipboardData.getData( 'text' )
		const mutlipleSourcesLines = filter( split( clipboardData, '\n' ), Boolean )

		if ( mutlipleSourcesLines.length < 1 ) {
			return
		}

		const newGroupsValue = [ ...value ]

		// Set the group value for the first multiple-sources-lines value
		newGroupsValue[ groupId ] = {
			...newGroupsValue[ groupId ],
			[ fieldId ]: mutlipleSourcesLines[ 0 ],
		}

		// Set the group value for the remaining multiple-sources-lines
		const remainingMultipleSourcesGroups = map(
			slice( mutlipleSourcesLines, 1 ),
			( line ) => ( {
				...newGroupsValue[ groupId ],
				[ fieldId ]: line,
			} )
		)

		const slicePosition = groupId + 1

		onChange( [
			...slice( newGroupsValue, 0, slicePosition ),
			...remainingMultipleSourcesGroups,
			...slice( newGroupsValue, slicePosition ),
		] )
	}

	useEffect( () => {
		// Add new group if value is empty
		if ( ! isUndefined( value ) && value.length === 0 ) {
			handleAdd()
		}
	}, [ value ] )

	return (
		<>
			{ map( value, ( group, groupId ) => (
				<div key={ groupId } className={ className }>
					{ map( fields, ( field ) => {
						const fieldId = `${ id }_${ groupId }_${ field.id }`

						const fieldProps = {
							...field,
							id: fieldId,
							value: group[ field.id ] || '',
							onChange: ( newValue ) => handleChange( newValue, groupId, field ),
							onPaste: ( event ) => handlePaste( event, groupId, field.id ),
						}

						return (
							<div
								key={ fieldId }
								className={ `group-item group-item-${ field.type }` }
							>
								<Field field={ fieldProps } />
							</div>
						)
					} ) }

					{ removeButton && (
						<Button
							{ ...removeButton }
							variant="remove-group"
							onClick={ () => handleRemove( groupId ) }
						/>
					) }
				</div>
			) ) }

			{ addButton && <Button onClick={ handleAdd } { ...addButton } /> }
		</>
	)
}
