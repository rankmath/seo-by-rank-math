/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import { serialize } from '@wordpress/blocks'
import apiFetch from '@wordpress/api-fetch'
import { Button } from '@wordpress/components'
import { select, dispatch, useSelect } from '@wordpress/data'
import { ShortcutProvider } from '@wordpress/keyboard-shortcuts'

/**
 * Internal dependencies
 */
import AITool from './AITool'
import Write from './Write'
import CopyButton from '../components/CopyButton'
import getBlockContent from '../helpers/getBlockContent'

/**
 * Function to get plain text from the blocks.
 *
 * @param {Array} blocks Blocks.
 */
const extractPlainText = ( blocks ) => {
	let plainText = ''

	blocks.forEach( ( block ) => {
		if ( 'rank-math/command' !== block.name && block.attributes && getBlockContent( block ) ) {
			plainText += getBlockContent( block ) + ' '
		}

		if ( block.innerBlocks && block.innerBlocks.length > 0 ) {
			plainText += extractPlainText( block.innerBlocks )
		}
	} )

	return plainText
}

/**
 * Content Editor actions to copy content.
 */
const ContentEditorActions = () => {
	const content = useSelect( () => select( 'core/editor' ).getEditedPostContent() )
	const plainText = extractPlainText( select( 'core/block-editor' ).getBlocks() )
	return (
		<div className="actions-wrapper">
			<CopyButton
				value={ plainText }
				disabled={ ! plainText }
				label={ __( 'Copy as Text', 'rank-math' ) }
			/>
			<CopyButton
				disabled={ ! plainText }
				value={ content }
				label={ __( 'Copy as Blocks', 'rank-math' ) }
				onClick={ true }
			/>

			<Button
				disabled={ ! plainText }
				onClick={ () => {
					const blocks = select( 'core/block-editor' ).getBlocks()

					const newBlock = wp.blocks.createBlock( 'rank-math/command', {
						content: '',
						className: 'rank-math-content-ai-command',
					} )
					blocks.push( newBlock )

					apiFetch( {
						method: 'POST',
						path: '/rankmath/v1/ca/createPost',
						data: {
							content: serialize( blocks ),
						},
					} )
						.catch( ( error ) => {
							console.log( error )
						} )
						.then( ( response ) => {
							window.location.href = response
						} )
				} }
				className="button is-secondary is-small"
			>
				<i className="rm-icon rm-icon-circle-plus"></i> { __( 'Create New Post', 'rank-math' ) }
			</Button>
		</div>
	)
}

// Content Editor Sidebar Panel.
const ContentEditorSidebar = ( props ) => {
	const [ hideSidebar, setHideSidebar ] = useState( false )
	const [ showWrite, setWrite ] = useState( true )

	return (
		<div className={ hideSidebar ? 'wp-block-column interface-interface-skeleton__sidebar has-collapsed' : 'wp-block-column interface-interface-skeleton__sidebar' }>
			{
				hideSidebar &&
				<Button
					className="collapsed"
					onClick={ () => ( setHideSidebar( false ) ) }
					icon="align-pull-right"
					title={ __( 'Expand Sidebar', 'rank-math' ) }
				/>
			}
			{
				! hideSidebar &&
				<div className="interface-complementary-area edit-post-sidebar rank-math-tabs">
					<div role="tablist" aria-orientation="horizontal" className="components-panel__header interface-complementary-area-header">
						<Button
							className={ showWrite ? 'is-active' : '' }
							onClick={ () => ( setWrite( true ) ) }
						>
							<i className="rm-icon rm-icon-edit" title={ __( 'Write', 'rank-math' ) }></i>
							<span>{ __( 'Write', 'rank-math' ) }</span>
						</Button>

						<Button
							className={ ! showWrite ? 'is-active' : '' }
							onClick={ () => ( setWrite( false ) ) }
						>
							<i className="rm-icon rm-icon-page" title={ __( 'AI Tools', 'rank-math' ) }></i>
							<span>{ __( 'AI Tools', 'rank-math' ) }</span>
						</Button>

						<Button
							onClick={ () => ( setHideSidebar( true ) ) }
							icon="no-alt"
							title={ __( 'Collapse Sidebar', 'rank-math' ) }
						/>
					</div>
					<div className={ ! showWrite ? 'rank-math-content-ai-wrapper rank-math-tab-content-write rank-math-tab-content-tools' : 'rank-math-content-ai-wrapper rank-math-tab-content-write' }>
						<div className="rank-math-tab-content-ai-tools">
							{ showWrite && <Write { ...props } /> }
							{ ! showWrite && <AITool { ...props } showMinimal={ true } isContentAIPage={ false } /> }
						</div>
					</div>
				</div>
			}
		</div>
	)
}

export default ( props ) => {
	setTimeout( () => {
		const editorData = document.getElementById( 'editor2' ).dataset
		wp.editPost.initializeEditor( 'editor', 'rm_content_editor', editorData.postId, JSON.parse( editorData.settings ), {} )

		// Disable Fullscreen mode.
		if ( select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ) ) {
			dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' )
		}
	}, 200 )

	return (
		<ShortcutProvider>
			<div className="wp-block-columns rank-math-content-ai-wrapper" id="rank-math-content-editor-page">
				<div className="wp-block-column">
					{ <ContentEditorActions /> }
					<div id="editor"></div>
				</div>
				<ContentEditorSidebar { ...props } />
			</div>
		</ShortcutProvider>
	)
}
