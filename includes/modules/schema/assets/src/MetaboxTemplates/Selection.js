/**
 * External dependencies
 */
import { get } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Button, Modal, PanelBody } from '@wordpress/components'
import { withDispatch, withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { generateValidSchema, mapCache } from '@schema/functions'

/**
 * Selection form template component.
 */
const Selection = ( { addSchema, isOpen } ) => {
	if ( ! isOpen ) {
		return null
	}

	return (
		<Modal
			title={ __( 'Schema Generator', 'rank-math' ) }
			closeButtonLabel={ __( 'Close', 'rank-math' ) }
			shouldCloseOnClickOutside={ false }
			className="rank-math-modal rank-math-schema-generator rank-math-schema-modal"
			onRequestClose={ () => {
				const { origin, pathname } = window.location
				window.location = origin + ( pathname
					.replace( 'post.php', 'edit.php?post_type=rank_math_schema' )
					.replace( 'post-new.php', 'edit.php?post_type=rank_math_schema' ) )
			} }
			overlayClassName="rank-math-modal-overlay"
		>
			<PanelBody initialOpen={ true }>
				<h4 className="rank-math-schema-section-title">{ __( 'Available Schema Types', 'rank-math' ) }</h4>

				<div className="rank-math-schema-catalog">
					{ mapCache.getTemplates().map( ( template, index ) => (
						<div key={ index } id="rank-math-schema-item" className="rank-math-schema-item row">
							<strong className="rank-math-schema-name">{ template.title }</strong>
							<span className="rank-math-schema-item-actions">
								<Button
									className="button rank-math-use-schema"
									isLink
									onClick={ () => addSchema( template ) }
								>
									<i className="rm-icon rm-icon-circle-plus"></i>
									<span>{ __( 'Use', 'rank-math' ) }</span>
								</Button>
							</span>
						</div>
					) ) }
				</div>
			</PanelBody>
		</Modal>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		return {
			...props,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			addSchema( schema ) {
				let map = get( schema, 'schema', false )
				if ( false === map ) {
					map = { '@type': schema.type, metadata: { type: 'template' } }
				}

				dispatch( 'rank-math' ).setEditingSchemaId( 'new-9999' )
				dispatch( 'rank-math' ).updateEditSchema( 'new-9999', generateValidSchema( map ) )
				dispatch( 'rank-math' ).toggleSchemaEditor( true )
			},
		}
	} )
)( Selection )
