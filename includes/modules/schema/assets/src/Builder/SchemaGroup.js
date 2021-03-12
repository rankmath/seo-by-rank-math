/**
 * External dependencies
 */
import classnames from 'classnames'
import { get, startCase, map as mapProperties, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { Button, ButtonGroup, TextControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import SchemaProperty from './SchemaProperty'
import { validateDependency } from '@schema/functions'
import DeleteConfirmation from '../DeleteConfirmation'

/**
 * Property index for repeater.
 *
 * @type {Object}
 */
const repeaterIndex = {}

/**
 * Schema group component.
 *
 * @param {Object} props This component's props.
 */
const SchemaGroup = ( props ) => {
	if ( false === validateDependency( props.data, props.schema ) ) {
		props.data.map.isHidden = true
		return null
	}

	const { parentId, isCustom, isPro, isMain = false } = props
	const { id, property, properties, map } = props.data
	const { addProperty, addGroup, removeGroup, propertyChange, duplicateGroup } = props.actions
	const field = get( map, 'field', { label: false } )
	const containerClasses = classnames(
		'schema-group-or-property-container schema-group-container',
		get( map, 'classes', false ),
		{
			'hide-property': 'metadata' === property,
			'is-group': map.isGroup,
			'is-array': map.isArray,
			'no-array-map': isUndefined( map.arrayMap ),
		}
	)

	if ( map.isArray ) {
		repeaterIndex[ id ] = 0
	}
	props.data.map.isHidden = false

	return (
		<div className={ containerClasses }>
			<div className="schema-group-or-property schema-group">
				{ applyFilters( 'rank_math_schema_before_fields', '', property ) }
				<div className="schema-group-header">
					{ ( () => {
						if ( props.isArray ) {
							repeaterIndex[ parentId ] += 1

							return (
								<div className="schema-property--label">
									{ startCase( property ) } { repeaterIndex[ parentId ] }
								</div>
							)
						}

						if ( ! isCustom && field.label ) {
							return (
								<div className="schema-property--label">
									{ field.label }
								</div>
							)
						}

						const value = 'WooCommerceProduct' !== property ? property : 'WooCommerce Product'
						return (
							<div className="schema-property--field">
								<TextControl
									value={ value }
									disabled={ ! isPro }
									onChange={ ( newProperty ) => {
										propertyChange( id, 'property', newProperty )
									} }
								/>
							</div>
						)
					} )() }
					<ButtonGroup className="schema-group--actions schema-group--actions--tr">
						<Button
							className="button rank-math-add-property"
							isLink
							onClick={ () => addProperty( id ) }
						>
							<i className="rm-icon rm-icon-circle-plus"></i>
							<span>{ __( 'Add Property', 'rank-math' ) }</span>
						</Button>
						<Button
							className="button rank-math-add-property-group"
							isLink
							onClick={ () => addGroup( id, map ) }
						>
							<i className="rm-icon rm-icon-circle-plus"></i>
							<span>{ __( 'Add Property Group', 'rank-math' ) }</span>
						</Button>
						{ ! isMain && (
							<Button
								className="button rank-math-duplicate-property-group"
								isLink
								onClick={ () => duplicateGroup( id, props.parentId, props.data ) }
							>
								<i className="rm-icon rm-icon-circle-plus"></i>
								<span>{ __( 'Duplicate Group', 'rank-math' ) }</span>
							</Button>
						) }
						<DeleteConfirmation
							key={ id }
							onClick={ () => removeGroup( id, props.parentId ) }
						>
							{ ( setClicked ) => {
								return (
									<Button
										className="button rank-math-delete-group"
										isLink
										onClick={ () => setClicked( true ) }
									>
										<i className="rm-icon rm-icon-trash"></i>
										<span>{ __( 'Delete', 'rank-math' ) }</span>
									</Button>
								)
							} }
						</DeleteConfirmation>
					</ButtonGroup>
				</div>
				<div className="schema-group--children">
					{ mapProperties( properties, ( prop, index ) => {
						return prop.map.isGroup ? (
							<SchemaGroup
								key={ index }
								data={ prop }
								parentId={ id }
								isArray={ map.isArray }
								isCustom={ isCustom }
								schema={ props.schema }
								actions={ props.actions }
								isPro={ isPro }
							/>
						) : (
							<SchemaProperty
								key={ index }
								data={ prop }
								parentId={ id }
								isCustom={ isCustom }
								schema={ props.schema }
								actions={ props.actions }
							/>
						)
					} ) }
				</div>
				{ isMain && (
					<div className="schema-group-footer">
						<ButtonGroup className="schema-group--actions schema-group--actions--tr">
							<Button
								className="button rank-math-add-property"
								isLink
								onClick={ () => addProperty( id ) }
							>
								<i className="rm-icon rm-icon-circle-plus"></i>
								<span>{ __( 'Add Property', 'rank-math' ) }</span>
							</Button>
							<Button
								className="button rank-math-add-property-group"
								isLink
								onClick={ () => addGroup( id, map ) }
							>
								<i className="rm-icon rm-icon-circle-plus"></i>
								<span>{ __( 'Add Property Group', 'rank-math' ) }</span>
							</Button>
							<DeleteConfirmation
								key={ id }
								onClick={ () => removeGroup( id, props.parentId ) }
							>
								{ ( setClicked ) => {
									return (
										<Button
											className="button rank-math-delete-group"
											isLink
											onClick={ () => setClicked( true ) }
										>
											<i className="rm-icon rm-icon-trash"></i>
											<span>{ __( 'Delete', 'rank-math' ) }</span>
										</Button>
									)
								} }
							</DeleteConfirmation>
						</ButtonGroup>
					</div>
				) }
			</div>
		</div>
	)
}

export default SchemaGroup
