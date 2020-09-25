/**
 * External dependencies
 */
import { get, has } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { applyFilters } from '@wordpress/hooks'
import { compose } from '@wordpress/compose'
import { withSelect } from '@wordpress/data'
import { Fragment } from '@wordpress/element'
import { Button, TextControl, Notice } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { validateDependency } from '@schema/functions'
import SchemaPropertyField from './SchemaPropertyField'
import DeleteConfirmation from '../DeleteConfirmation'

/**
 * Get schema property value.
 *
 * @param {Object} props This component's props.
 * @param {Object} field This field.
 */
const getSchemaPropertyValue = ( props, field ) => {
	let { property, value } = props.data
	const { metadata } = props.schema

	if ( value === get( field, 'placeholder', '' ) ) {
		value = ''
	}

	if ( 'reviewLocation' === property && has( metadata, 'reviewLocation' ) ) {
		value = metadata.reviewLocation
	}

	if ( 'unpublish' === property && has( metadata, 'unpublish' ) ) {
		value = metadata.unpublish
	}

	if ( props.isPro && '[rank_math_rich_snippet]' === value ) {
		value = '[rank_math_rich_snippet id="' + metadata.shortcode + '"]'
	}

	return applyFilters( 'rank_math_schema_property_value', value, props )
}

/**
 * Schema property component.
 *
 * @param {Object} props This component's props.
 */
const SchemaProperty = ( props ) => {
	if ( false === validateDependency( props.data, props.schema ) ) {
		props.data.map.isHidden = true
		return null
	}

	const { property, id, map } = props.data
	const { removeProperty, propertyChange, duplicateProperty } = props.actions
	const field = get( map, 'field', { label: false } )
	const fieldProps = { ...field }
	const value = getSchemaPropertyValue( props, field )

	if ( map.isRequired ) {
		if ( field.label ) {
			fieldProps.label = <Fragment>{ field.label } <span>*</span></Fragment>
		}

		if ( ! field.placeholder ) {
			fieldProps.required = 'required'
		}
	}

	if ( props.isCustom ) {
		fieldProps.type = 'text'
		delete fieldProps.label
	}

	const containerClasses = classnames(
		'schema-group-or-property-container schema-property-container',
		get( field, 'classes', false ),
		{ 'hide-property': '@type' === property }
	)

	props.data.map.isHidden = false

	return (
		<div className={ containerClasses }>
			<div className="schema-group-or-property schema-property">
				<div className="schema-property--body">
					{ ( () => {
						if ( ! props.isCustom && field.label ) {
							return null
						}

						return (
							<div className="schema-property--field">
								<TextControl
									value={ property }
									onChange={ ( newProperty ) => {
										propertyChange(
											id,
											'property',
											newProperty
										)
									} }
								/>
							</div>
						)
					} )() }
					<div className="schema-property--value">
						<SchemaPropertyField
							value={ value }
							{ ...fieldProps }
							onChange={ ( newValue ) => {
								propertyChange( id, 'value', newValue )
							} }
						/>
						{ has( fieldProps, 'notice' ) && (
							<Notice isDismissible={ false } { ...fieldProps.notice } >
								{ fieldProps.notice.content }
							</Notice>
						) }
					</div>
					{ ! map.isRequired && (
						<div className="schema-property--header">
							<Button
								isSecondary
								className="button rank-math-duplicate-property"
								onClick={ () => duplicateProperty( id, props.parentId, props.data ) }
							>
								<i className="rm-icon rm-icon-copy"></i>
							</Button>

							<DeleteConfirmation
								key={ id }
								onClick={ () => removeProperty( id, props.parentId ) }
							>
								{ ( setClicked ) => {
									return (
										<Button
											isSecondary
											className="button rank-math-delete-group"
											onClick={ () => setClicked( true ) }
										>
											<i className="rm-icon rm-icon-trash"></i>
										</Button>
									)
								} }
							</DeleteConfirmation>
						</div>
					) }
				</div>
			</div>
		</div>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		return {
			...props,
			isPro: select( 'rank-math' ).isPro(),
		}
	} )
)( SchemaProperty )
