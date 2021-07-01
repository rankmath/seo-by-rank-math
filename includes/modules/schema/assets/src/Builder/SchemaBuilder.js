/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import classnames from 'classnames'
import jQuery from 'jquery'
import { get, cloneDeep, forEach, isArray, isEmpty, has } from 'lodash'
import { v4 as uuid } from 'uuid'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment, Component } from '@wordpress/element'
import { Button } from '@wordpress/components'
import { withSelect, withDispatch } from '@wordpress/data'
import { doAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import SchemaGroup from './SchemaGroup'
import {
	findProperty,
	generateSchemaFromMap,
	getGroupDefault,
	getPropertyDefault,
	processSchema,
	changeIds,
} from '@schema/functions'

/**
 * Schema builder component.
 */
class SchemaBuilder extends Component {
	/**
	 * Constructor.
	 */
	constructor() {
		super( ...arguments )
		this.options = get( this.props.data, 'metadata', {} )
		this.state = { data: this.props.data, loading: false, showNotice: false, postId: rankMath.objectID }
		this.setState = this.setState.bind( this )
		this.templateSaveCount = 0
		this.isEditingTemplate = get( rankMath, 'isTemplateScreen', false )
	}

	getWrapperClasses() {
		let knowledgegraphType = get( rankMath, 'knowledgegraphType', false )
		knowledgegraphType = false === knowledgegraphType ? 'empty' : 'local-' + knowledgegraphType

		let schemaType = get( this.props.data, 'property', '' )
		if ( isArray( schemaType ) ) {
			schemaType = schemaType.join( '-' )
		}
		schemaType = 'schema-' + schemaType.toLowerCase()

		return classnames( 'schema-builder', schemaType, {
			'schema-template-pre-defined': 'template' === this.options.type,
			'schema-template-custom': 'custom' === this.options.type,
			[ `${ knowledgegraphType }` ]: 'template' === this.options.type,
		} )
	}

	/**
	 * Redenr this component.
	 *
	 * @return {Component} Schema builder.
	 */
	render() {
		return (
			<form className={ this.getWrapperClasses() }>
				<SchemaGroup
					data={ this.state.data }
					schema={ this.state.data }
					isPro={ this.props.isPro }
					parentId={ null }
					isMain={ true }
					isArray={ false }
					isCustom={ 'custom' === this.options.type }
					actions={ {
						addGroup: this.addGroup,
						addProperty: this.addProperty,
						removeGroup: this.removeGroup,
						removeProperty: this.removeProperty,
						propertyChange: this.propertyChange,
						duplicateGroup: this.duplicateGroup,
						duplicateProperty: this.duplicateProperty,
					} }
				/>

				<div className="schema-builder-save-as">
					{ this.props.isPro && (
						<Fragment>
							{ 'custom' !== this.options.type && (
								<Button
									isSecondary
									onClick={ () => this.props.toggleMode( this.props.id, this.state.data ) }
								>
									{ __( 'Advanced Editor', 'rank-math' ) }
								</Button>
							) }
							{ ! this.isEditingTemplate && (
								<Button
									isSecondary
									className={ this.state.loading ? 'save-as-template saving' : 'save-as-template' }
									onClick={ () => {
										if ( this.templateSaveCount >= 1 && ! confirm( __( 'Each save will create a new template.', 'rank-math' ) ) ) {
											return
										}

										this.templateSaveCount += 1
										this.props.saveTemplate( this.state.data, this.setState )
									} }
								>
									{ this.state.showNotice ? __( 'Template saved.', 'rank-math' ) : __( 'Save as Template', 'rank-math' ) }
								</Button>
							) }
							{ this.state.showNotice && (
								<div className="rank-math-save-template-confirmation">{ __( 'Template saved.', 'rank-math' ) }</div>
							) }
						</Fragment>
					) }
					{ this.isEditingTemplate && (
						<Button
							isPrimary
							className="button"
							onClick={ () => this.props.saveTemplate( this.state.data, this.setState, this.state.postId ) }
						>
							{ this.state.loading ? __( 'Saving', 'rank-math' ) : ( this.state.showNotice ? __( 'Saved', 'rank-math' ) : __( 'Save', 'rank-math' ) ) }
						</Button>
					) }
					{ ! this.isEditingTemplate && (
						<Button
							isPrimary
							className="button"
							onClick={ () => this.props.saveSchema( this.props.id, this.state.data ) }
						>
							{ 'term' === rankMath.objectType ? __( 'Save for this Term', 'rank-math' ) : __( 'Save for this Post', 'rank-math' ) }
						</Button>
					) }
				</div>
			</form>
		)
	}

	/**
	 * Add an empty group into schema.
	 *
	 * @param {string} parentId Parent group id.
	 * @param {Object} map      Group map params.
	 */
	addGroup = ( parentId, map ) => {
		const data = { ...this.state.data }
		const parent = findProperty( parentId, data )
		const { isArray, arrayMap = false, arrayProps = {} } = map

		const group =
			isArray && arrayMap
				? generateSchemaFromMap( arrayMap, arrayProps )
				: getGroupDefault()

		parent.properties.push( group )
		this.setState( { data } )
	}

	/**
	 * Add an empty property into schema.
	 *
	 * @param {string} parentId Parent group id.
	 */
	addProperty = ( parentId ) => {
		const data = { ...this.state.data }
		const parent = findProperty( parentId, data )
		parent.properties.push( getPropertyDefault() )
		this.setState( { data } )
	}

	/**
	 * Duplicate group.
	 *
	 * @param  {string} groupId  Group id to duplicate.
	 * @param  {string} parentId Parent group id to insert into.
	 * @param  {Object} group    Group data.
	 */
	duplicateGroup = ( groupId, parentId, group ) => {
		const data = { ...this.state.data }
		const parent = findProperty( parentId, data )
		const duplicate = cloneDeep( group )
		const index = parent.properties.findIndex(
			( prop ) => prop.id === groupId
		)

		parent.properties.splice( index, 0, changeIds( duplicate ) )
		this.setState( { data } )
	}

	/**
	 * Duplicate property.
	 *
	 * @param  {string} propertyId Property id to duplicate.
	 * @param  {string} parentId   Parent group id to insert into.
	 * @param  {Object} property   Property data.
	 */
	duplicateProperty = ( propertyId, parentId, property ) => {
		const data = { ...this.state.data }
		const parent = findProperty( parentId, data )
		const duplicate = { ...property }
		const index = parent.properties.findIndex(
			( prop ) => prop.id === propertyId
		)

		duplicate.id = `p-${ uuid() }`

		parent.properties.splice( index, 0, duplicate )
		this.setState( { data } )
	}

	/**
	 * Remove group from schema.
	 *
	 * @param  {string} groupId  Group id to remove.
	 * @param  {string} parentId Parent group id to remove from.
	 */
	removeGroup = ( groupId, parentId ) => {
		const data = { ...this.state.data }
		const parent = findProperty( parentId, data )

		// Delete Parent group.
		if ( parent.id === groupId ) {
			this.setState( { data: getGroupDefault() } )
			return
		}

		const index = parent.properties.findIndex(
			( group ) => group.id === groupId
		)

		parent.properties.splice( index, 1 )
		this.setState( { data } )
	}

	/**
	 * Remove property from schema.
	 *
	 * @param  {string} propertyId Property id to remove.
	 * @param  {string} parentId   Parent group id to remove from.
	 */
	removeProperty = ( propertyId, parentId ) => {
		const data = { ...this.state.data }
		const parent = findProperty( parentId, data )
		const index = parent.properties.findIndex(
			( property ) => property.id === propertyId
		)

		parent.properties.splice( index, 1 )
		this.setState( { data } )
	}

	/**
	 * Update property data.
	 *
	 * @param  {string} propertyId Property or Group id to update.
	 * @param  {string} property   Property name to update.
	 * @param  {string} value      Property value.
	 */
	propertyChange = ( propertyId, property, value ) => {
		const data = { ...this.state.data }
		const parent = findProperty( propertyId, data )
		Object.assign( parent, { [ property ]: value } )
		if ( ! isEmpty( data.metadata ) && has( data.metadata, parent.property ) ) {
			data.metadata[ parent.property ] = value
		}

		this.setState( { data } )

		doAction( 'rank_math_property_changed', data, parent, this.setState )
	}
}

SchemaBuilder.defaultProps = {
	query: null,
	fields: [],
	onQueryChange: null,
}

SchemaBuilder.propTypes = {
	query: PropTypes.object,
	fields: PropTypes.array.isRequired,
	onQueryChange: PropTypes.func,
}

export default compose(
	withSelect( ( select, props ) => {
		const selected = select( 'rank-math' ).getEditingSchema()
		const schemas = select( 'rank-math' ).getEditSchemas()
		return {
			...props,
			...selected,
			schemas,
			isPro: select( 'rank-math' ).isPro(),
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		const { onSave = false, isPro, schemas } = props
		return {
			toggleMode( id, data ) {
				if ( confirm( __( "Are you sure you want to convert? You can't use simple mode for this edited Schema.", 'rank-math' ) ) ) {
					const schema = { ...data }
					schema.metadata.type = 'custom'
					dispatch( 'rank-math' ).updateEditSchema( id, schema )
				}
			},
			saveSchema( id, data ) {
				const form = jQuery( 'form.schema-builder' ).get( 0 )
				if ( ! form.checkValidity() ) {
					form.reportValidity()
					return
				}

				if ( ! isPro ) {
					forEach( schemas, ( value, key ) => {
						if ( id !== key ) {
							dispatch( 'rank-math' ).deleteSchema( key )
						}
					} )
				}

				const schema = processSchema( data )
				dispatch( 'rank-math' ).updateEditSchema( id, data )
				dispatch( 'rank-math' ).saveSchema( id, schema )

				if ( onSave ) {
					onSave( id, schema )
				}

				dispatch( 'rank-math' ).schemaUpdated( true )
				dispatch( 'rank-math' ).toggleSchemaTemplates( false )
				dispatch( 'rank-math' ).toggleSchemaEditor( false )
			},
			saveTemplate( data, setState, postId ) {
				dispatch( 'rank-math' ).saveTemplate( processSchema( data ), setState, postId )
			},
		}
	} )
)( SchemaBuilder )
