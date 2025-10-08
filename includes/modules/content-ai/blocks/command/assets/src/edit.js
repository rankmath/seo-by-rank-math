/**
 * WordPress dependencies
 */
import {
	RichText,
	useBlockProps,
	BlockControls,
	store as blockEditorStore,
} from '@wordpress/block-editor'
import { useDispatch } from '@wordpress/data'
import { useRef, useEffect } from '@wordpress/element'
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import hasError from '../../../../assets/src/helpers/hasError'
import insertCommandBox, { useBlock, regenerateOutput, writeMore } from '../../../../assets/src/shortcutCommand/insertCommandBox'
import getWriteAttributes from '../../../../assets/src/helpers/getWriteAttributes'
import getBlockContent from '../../../../assets/src/helpers/getBlockContent'

const getErrorMessage = () => {
	// This function can be simplified now that the error message comes from the API.
	// It's still useful for showing general errors not from the API.

	if ( ! rankMath.contentAI.isUserRegistered ) {
		return (
			<>
				{ __( 'Start using Content AI by connecting your RankMath account.', 'rank-math' ) }
				<a href={ rankMath.connectSiteUrl }>{ __( 'Connect Now', 'rank-math' ) }</a>
			</>
		)
	}

	if ( ! rankMath.contentAI.plan ) {
		return (
			<>
				{ __( 'You do not have a Content AI plan.', 'rank-math' ) }
				<a href="https://rankmath.com/kb/how-to-use-content-ai/?play-video=ioPeVIntJWw&utm_source=Plugin&utm_medium=Buy+Plan+Button&utm_campaign=WP">
					{ __( 'Choose your plan', 'rank-math' ) }
				</a>
			</>
		)
	}

	return (
		<>
			{ __( 'You have exhausted your Content AI Credits.', 'rank-math' ) }
			<a href="https://rankmath.com/kb/how-to-use-content-ai/?play-video=ioPeVIntJWw&utm_source=Plugin&utm_medium=Buy+Credits+Button&utm_campaign=WP" target="_blank" rel="noreferrer">
				{ __( 'Get more', 'rank-math' ) }
			</a>
		</>
	)
}

export default ( {
	attributes,
	onReplace,
	setAttributes,
	clientId,
} ) => {
	// Note the addition of `hasApiError` in the attributes destructuring.
	const { content, isAiGenerated, hasApiError, endpoint, params } = attributes
	const blockProps = useBlockProps( {
		className: 'rank-math-content-ai-command',
	} )
	const { updateBlockAttributes, removeBlock } = useDispatch( blockEditorStore )
	const blockHook = useBlock // assign the hook to a variable

	const contentEditableRef = useRef( null )
	useEffect( () => {
		const { current: contentEditable } = contentEditableRef
		if ( ! contentEditable ) {
			return
		}
		contentEditable.focus()

		const range = document.createRange()
		const selection = window.getSelection()
		range.selectNodeContents( contentEditable )
		range.collapse( false )
		selection.removeAllRanges()
		selection.addRange( range )
	}, [] )

	const handleRunCommand = () => {
		const text = getBlockContent( { attributes } )

		if ( ! text.trim() ) {
			return
		}

		updateBlockAttributes(
			clientId,
			{
				content: '',
				className: '',
			}
		)

		insertCommandBox( 'Write', getWriteAttributes( text ), clientId )
	}

	const handleDismissCommand = () => {
		removeBlock( clientId )
	}

	const handleUseBlock = () => {
		blockHook( clientId, attributes )
	}

	const handleRegenerateOutput = () => {
		regenerateOutput( clientId, endpoint, params )
	}

	const handleWriteMore = () => {
		writeMore( clientId )
	}

	const handleKeyDown = ( event ) => {
		if ( event.key === 'Enter' && ! isAiGenerated ) {
			event.preventDefault()
			handleRunCommand()
		}
	}

	const contentTrimmed = content.replace( /(<([^>]+)>)/ig, '' ).trim()

	const renderActionButtons = () => {
		// This is the correct condition to show the dismiss button on API error.
		if ( hasApiError ) {
			return (
				<button
					className="button button-small rank-math-content-ai-dismiss"
					title={ __( 'Dismiss', 'rank-math' ) }
					onClick={ handleDismissCommand }
					contentEditable={ false }
				>
					{ __( 'Dismiss', 'rank-math' ) }
				</button>
			)
		}

		if ( isAiGenerated ) {
			return (
				<div className="rank-math-content-ai-command-buttons">
					<button
						className="button button-small rank-math-content-ai-use"
						onClick={ handleUseBlock }
					>
						<span>{ __( 'Use', 'rank-math' ) }</span>
					</button>
					<button
						className="button button-small rank-math-content-ai-regenerate"
						onClick={ handleRegenerateOutput }
					>
						<span>{ __( 'Regenerate', 'rank-math' ) }</span>
					</button>
					<button
						className="button button-small rank-math-content-ai-write-more"
						onClick={ handleWriteMore }
					>
						<span>{ __( 'Write More', 'rank-math' ) }</span>
					</button>
				</div>
			)
		}

		// This is the condition for the initial command box.
		if ( contentTrimmed.length > 0 ) {
			return (
				<>
					<button
						className="rank-math-content-ai-command-button"
						title={ __( 'Click or Press Enter', 'rank-math' ) }
						onClick={ handleRunCommand }
						contentEditable={ false }
					>
						<i className="rm-icon rm-icon-enter-key"></i>
					</button>
					<button
						className="rank-math-command-dismiss-button"
						title={ __( 'Dismiss', 'rank-math' ) }
						onClick={ handleDismissCommand }
						contentEditable={ false }
					>
						{ __( 'Close', 'rank-math' ) }
					</button>
				</>
			)
		}

		return null
	}

	return (
		<div { ...blockProps }>
			<BlockControls />
			{
				hasError() &&
				<div className="rich-text" ref={ contentEditableRef }>
					{ getErrorMessage() }
				</div>
			}

			{ ! hasError() &&
				<>
					<RichText
						tagName="div"
						allowedFormats={ [] }
						value={ content }
						onChange={ ( newContent ) => {
							setAttributes( { content: newContent } )
						} }
						onSplit={ () => false }
						onReplace={ onReplace }
						data-empty={ content ? false : true }
						onKeyDown={ handleKeyDown }
						ref={ contentEditableRef }
					/>

					<div className="rank-math-content-ai-command-buttons">
						{ renderActionButtons() }
					</div>
				</>
			}
		</div>
	)
}