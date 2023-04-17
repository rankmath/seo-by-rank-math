/**
 * External dependencies
 */
import { get, map, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Button, withFilters } from '@wordpress/components'
import { withSelect, withDispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { getSnippetIcon } from '@helpers/snippetIcon'
import getLink from '@helpers/getLink'
import DeleteConfirmation from './DeleteConfirmation'

/**
 * Show the lists of schemas used in the Current Post/Term.
 *
 * @param {Object}     props               The props data.
 * @param {Object}     props.schemas       Schema data.
 * @param {function()} props.edit          A callback to run when clicked on Edit button.
 * @param {function()} props.trash         A callback to run when clicked on Trash button.
 * @param {function()} props.preview       A callback to run when clicked on Preview button.
 * @param {boolean}    props.showProNotice Whether to show Pro notice.
 */
const SchemaList = ( { schemas, edit, trash, preview, showProNotice } ) => {
	if ( isEmpty( schemas ) ) {
		return null
	}

	return (
		<div className="rank-math-schema-in-use">
			<h4 className="rank-math-schema-section-title">{ __( 'Schema in Use', 'rank-math' ) }</h4>
			{ showProNotice && (
				<div className="components-notice rank-math-notice is-warning">
					<div className="components-notice__content">
						{ __( 'Multiple Schemas are allowed in the', 'rank-math' ) } <a href={ getLink( 'pro', 'Schema Tab Notice' ) } rel="noreferrer noopener" target="_blank"><strong>{ __( 'PRO Version', 'rank-math' ) }</strong></a>
					</div>
				</div>
			) }
			{ map( schemas, ( schema, id ) => {
				return (
					<div key={ id } id="rank-math-schema-item" className="rank-math-schema-item row">
						<strong className="rank-math-schema-name">
							<i className={ getSnippetIcon( schema[ '@type' ] ) }></i>
							{ get( schema, 'metadata.title', schema[ '@type' ] ) }
						</strong>
						<span className="rank-math-schema-item-actions">
							<Button
								className="button rank-math-edit-schema"
								isLink
								onClick={ () => edit( id ) }
							>
								<i className="rm-icon rm-icon-edit"></i>
								<span>{ __( 'Edit', 'rank-math' ) }</span>
							</Button>
							<Button
								className="button rank-math-preview-schema"
								isLink
								onClick={ () => preview( id ) }
							>
								<i className="rm-icon rm-icon-eye"></i>
								<span>{ __( 'Preview', 'rank-math' ) }</span>
							</Button>
							<DeleteConfirmation
								key={ id }
								onClick={ () => trash( id ) }
							>
								{ ( setClicked ) => {
									return (
										<Button
											isLink
											className="button rank-math-delete-schema"
											onClick={ () => setClicked( true ) }
										>
											<i className="rm-icon rm-icon-trash"></i>
											<span>{ __( 'Delete', 'rank-math' ) }</span>
										</Button>
									)
								} }
							</DeleteConfirmation>
						</span>
					</div>
				)
			}
			) }
		</div>
	)
}

const schemaList = withFilters( 'rankMath.schema.SchemaList' )( SchemaList )

export default compose(
	withSelect( ( select ) => {
		const schemas = select( 'rank-math' ).getSchemas()
		const count = Object.keys( schemas ).length

		// @TODO:: it's reset from where when the modal is open and no selection is made.
		// Always overrides to default if the user doesn't interract with the select.
		console.log("||||||||||||||||| SchemaList.js ||||||||||||||||||||||")
		console.log("||||||||||||||||| SchemaList.js ||||||||||||||||||||||")
		console.log(schemas['schema-16']['metadata']['unpublish'])
		console.log("||||||||||||||||| SchemaList.js ||||||||||||||||||||||")
		console.log("||||||||||||||||| SchemaList.js ||||||||||||||||||||||")

		return {
			schemas,
			showProNotice: 1 <= count,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			trash( id ) {
				dispatch( 'rank-math' ).deleteSchema( id )
			},
			edit( id ) {
				dispatch( 'rank-math' ).setEditingSchemaId( id )
				dispatch( 'rank-math' ).toggleSchemaEditor( true )
			},
			preview( id ) {
				dispatch( 'rank-math' ).setEditingSchemaId( id )
				dispatch( 'rank-math' ).setEditorTab( 'codeValidation' )
				dispatch( 'rank-math' ).toggleSchemaEditor( true )
			},
		}
	} )
)( schemaList )
