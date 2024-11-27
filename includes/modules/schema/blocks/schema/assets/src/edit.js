/**
 * External dependencies
 */
import { startCase, forEach } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useBlockProps, InspectorControls } from '@wordpress/block-editor'
import { TextControl, PanelBody } from '@wordpress/components'
import ServerSideRender from '@wordpress/server-side-render'

export default ( {
	attributes,
	setAttributes,
} ) => {
	const blockProps = useBlockProps()
	const controllers = []

	if ( ! attributes.post_id ) {
		attributes.post_id = rankMath.objectID
		setAttributes( { post_id: rankMath.objectID } )
	}
	forEach( attributes, ( attribute, slug ) => {
		if ( 'post_id' === slug ) {
			controllers.push(
				<TextControl
					key={ slug }
					label={ __( startCase( slug ), 'rank-math' ) }
					value={ attributes[ slug ] }
					type="number"
					min={ 1 }
					step={ 1 }
					onChange={ ( newID ) => {
						const attrs = {}
						attrs[ slug ] = newID ? newID : rankMath.objectID
						setAttributes( attrs )
					} }
				/>
			)
			return
		}

		if ( 'className' !== slug ) {
			controllers.push(
				<TextControl
					key={ slug }
					label={ __( startCase( slug ), 'rank-math' ) }
					value={ attributes[ slug ] }
					type="string"
					onChange={ ( nextID ) => {
						const attrs = {}
						attrs[ slug ] = nextID
						setAttributes( attrs )
					} }
				/>
			)
		}
	} )

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'rank-math' ) }>
					{ controllers }
				</PanelBody>
			</InspectorControls>

			<ServerSideRender
				block="rank-math/rich-snippet"
				attributes={ attributes }
			/>
		</div>
	)
}
