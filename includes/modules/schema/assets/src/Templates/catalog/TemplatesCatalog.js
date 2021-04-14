/**
 * External dependencies
 */
import { v4 as uuid } from 'uuid'
import classnames from 'classnames'
import { get, uniqueId, isEmpty, findKey } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Button } from '@wordpress/components'
import { applyFilters } from '@wordpress/hooks'
import { withDispatch, withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { getSnippetIcon } from '@helpers/snippetIcon'
import { generateValidSchema } from '@schema/functions'

/**
 * Template list.
 *
 * @param {Object} props                The props data.
 * @param {string} props.search         Searched string.
 * @param {Array}  props.templates      Lists of templates.
 * @param {boolean} props.isPro         Whether the Pro plugin is active.
 * @param {function()} props.addSchema  A callback to run when clicked on add button.
 * @param {function()} props.editSchema A callback to run when clicked on edit button.
 * @param {Array} props.primarySchema   Primary Schema data.
 *
 */
const TemplatesCatalog = ( { search, templates, isPro, addSchema, editSchema, primarySchema } ) => {
	if ( '' !== search ) {
		templates = templates.filter( ( template ) => template.title.toLowerCase().includes( search ) )
	}

	const primaryType = primarySchema ? applyFilters( 'rank_math_schema_type', primarySchema.type ) : ''

	return (
		<div className="rank-math-schema-catalog">
			{ templates.map( ( template, index ) => {
				const isPrimary = ! isPro && primaryType === template.type
				const classes = classnames(
					'rank-math-schema-item rank-math-use-schema row button',
					{ 'in-use': isPrimary }
				)

				return (
					<div id="rank-math-schema-list-wrapper" key={ index }>
						<Button
							key={ index }
							id="rank-math-schema-item"
							className={ classes }
							href="#"
							isLink
							onClick={ () => isPrimary ? editSchema( primarySchema.id ) : addSchema( template ) }
						>
							<input
								type="radio"
								name="primarySchema"
								value={ template.type }
								checked={ primaryType === template.type }
								onChange={ () => addSchema( template ) }
							/>
							<span className="rank-math-schema-name">
								<i className={ getSnippetIcon( template.type ) }></i>
								{ template.title }
							</span>
							<span className="button rank-math-schema-item-actions">
								<i className="rm-icon rm-icon-circle-plus"></i>
								<span>{ __( 'Use', 'rank-math' ) }</span>
							</span>
						</Button>
					</div>
				)
			} ) }
		</div>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		const isPro = select( 'rank-math' ).isPro()
		const schemas = select( 'rank-math' ).getSchemas()
		let primarySchema = findKey( schemas, 'metadata.isPrimary' )
		primarySchema = isEmpty( primarySchema ) ? {} : {
			id: primarySchema,
			type: schemas[ primarySchema ][ '@type' ],
		}

		return {
			...props,
			primarySchema,
			isPro,
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		const { isPro, primarySchema = false } = props
		let isPrimary = isPro && isEmpty( primarySchema ) ? true : false
		if ( ! isPro ) {
			isPrimary = true
		}

		return {
			addSchema( schema ) {
				const id = uniqueId( 'new-' )
				let map = get( schema, 'schema', false )
				if ( false === map ) {
					map = {
						'@type': schema.type,
						metadata: {
							type: 'template',
							shortcode: `s-${ uuid() }`,
						},
					}
				}

				map.metadata.isPrimary = isPrimary

				dispatch( 'rank-math' ).setEditingSchemaId( id )
				dispatch( 'rank-math' ).updateEditSchema( id, generateValidSchema( map ) )
				dispatch( 'rank-math' ).toggleSchemaEditor( true )
			},
			editSchema( id ) {
				dispatch( 'rank-math' ).setEditingSchemaId( id )
				dispatch( 'rank-math' ).toggleSchemaEditor( true )
			},
		}
	} )
)( TemplatesCatalog )
