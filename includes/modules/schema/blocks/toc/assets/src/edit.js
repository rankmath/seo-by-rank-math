/**
 * External dependencies
 */
import { isUndefined, map, includes, remove } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import {
	useBlockProps,
	RichText,
	store as blockEditorStore,
} from '@wordpress/block-editor'
import { store as editorStore } from '@wordpress/editor'
import { useDispatch, useSelect } from '@wordpress/data'
import { Placeholder, Spinner } from '@wordpress/components'
import { useEffect, useState } from '@wordpress/element'
import ServerSideRender from '@wordpress/server-side-render';


/**
 * Internal dependencies
 */
import InspectControls from './inspectControls'
import Toolbar from './toolbar'

export default ( {
	attributes,
	setAttributes,
	context,
} ) => {

	const { isSaving, isSavingNonPostEntityChanges } = useSelect( ( select ) => {
		const { isSavingPost, isSavingNonPostEntityChanges } = select( editorStore );
		return {
			isSaving: isSavingPost(),
			isSavingNonPostEntityChanges: isSavingNonPostEntityChanges(),
		}
	})


	const blockProps = useBlockProps()
	const { postId } = context;

	// State to monitor edit heading links.
	const [ edit, toggleEdit ] = useState( false )
	const [ excludeHeading, toggleExcludeHeading ] = useState( {} )
	if ( ! attributes.listStyle ) {
		setAttributes( { listStyle: rankMath.listStyle } )
	}

	const tocTitle = attributes.title ?? rankMath.tocTitle
	const excludeHeadings = ! isUndefined( attributes.excludeHeadings ) ? attributes.excludeHeadings : rankMath.tocExcludeHeadings

	const setExcludeHeadings = ( headingLevel ) => {
		if ( includes( excludeHeadings, headingLevel ) ) {
			remove( excludeHeadings, ( heading ) => {
				return heading === headingLevel
			} )
		} else {
			excludeHeadings.push( headingLevel )
		}
		setAttributes( { excludeHeadings } )
		toggleExcludeHeading( ! excludeHeading )
	}
	
	return (
		<div { ...blockProps }>
			{ ( isSaving || isSavingNonPostEntityChanges ) ? <Spinner /> : (
				<ServerSideRender
					block="rank-math/toc-block"
					attributes={{
						postId: postId
					}}
				/>
			)}
			<RichText
				tagName={ attributes.titleWrapper }
				value={ tocTitle }
				onChange={ ( newTitle ) => {
					setAttributes( { title: newTitle } )
				} }
				placeholder={ __( 'Enter a title', 'rank-math' ) }
			/>
			<Toolbar setAttributes={ setAttributes } />
			<InspectControls attributes={ attributes } setAttributes={ setAttributes } excludeHeadings={ excludeHeadings } setExcludeHeadings={ setExcludeHeadings } />
		</div>
	)
}
