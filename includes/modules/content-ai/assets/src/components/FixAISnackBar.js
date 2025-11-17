/**
 * External dependencies
 */
import { includes, trim, forEach, isEmpty, isBoolean, mapValues } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'
import { compose } from '@wordpress/compose'
import { useEffect, useState } from '@wordpress/element'
import { withSelect, withDispatch, dispatch } from '@wordpress/data'
import { close } from '@wordpress/icons'
import { serialize } from '@wordpress/blocks'
import { applyFilters, doAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { sanitizePermalink } from '@helpers/sanitize'
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'
import stripScripts from '@helpers/stripScripts'
import getData from '../helpers/getData'
import { replaceContent, restoreOriginalContent, removeHighlighting } from '../helpers/fixAnalysisTest'
import { approveIcon, regenerateIcon, rejectIcon, sparkleIcon, undoIcon } from '../helpers/icons'
import { removeDataBlockID } from '../helpers/tinymceUtils'

/**
 * Updates the value in the specified field for the selected test.
 *
 * @param {string}  result   The value to update in the field.
 * @param {Object}  props    An object containing the component properties, including test-specific details.
 * @param {boolean} isReject A boolean indicating whether the function was called after clicking the Reject button.
 */
const updateTest = ( result, props, isReject = false ) => {
	const { id, previousResults } = props
	if ( includes( [ 'keywordInTitle', 'titleStartWithKeyword', 'titleSentiment', 'titleHasNumber', 'titleHasPowerWords' ], id ) ) {
		result = ! result && isReject ? props.seo_title : result
		dispatch( 'rank-math' ).updateSerpTitle( result )
		dispatch( 'rank-math' ).updateTitle( result )
		dispatch( 'rank-math' ).toggleSnippetEditor( true )
	}

	if ( id === 'keywordInMetaDescription' ) {
		result = ! result && isReject ? props.description : result
		dispatch( 'rank-math' ).updateSerpDescription( result )
		dispatch( 'rank-math' ).updateDescription( result )
		dispatch( 'rank-math' ).toggleSnippetEditor( true )
	}

	if ( id === 'keywordInPermalink' ) {
		result = ! result && isReject ? props.url : result
		dispatch( 'rank-math' ).updatePermalink( result )
		rankMathEditor.updatePermalink( sanitizePermalink( result ), true )
		rankMathEditor.updatePermalinkSanitize( sanitizePermalink( result ) )
		dispatch( 'rank-math' ).toggleSnippetEditor( true )
	}

	if ( props.isContentTest ) {
		if ( ! result && isReject ) {
			if ( rankMath.currentEditor === 'elementor' ) {
				doAction( 'rank_math_content_test_reject', props.blocks )
				return
			}

			restoreOriginalContent( props.blocks, props.originalContent )
			return
		}

		restoreOriginalContent( props.blocks, props.originalContent ) // This is needed to reset the content before highlighting the changes. @Todo find a better way of handing this.
		replaceContent( JSON.parse( result ), props.blocks )
	}

	if ( isReject ) {
		return
	}

	previousResults.push( result )
	dispatch( 'rank-math-content-ai' ).updatePreviousResults( previousResults )
}

/**
 * Get a flat array of blocks with their clientId as keys and content as values.
 *
 * @param {Array} data List of Blocks with attributes.
 *
 * @return {Object} An object where keys are clientId and values are block content.
 */
const getFlatBlocks = ( data ) => {
	const processBlocks = ( blocks, result = {} ) => {
		forEach( blocks, ( block ) => {
			if ( block.innerBlocks && block.innerBlocks.length > 0 ) {
				processBlocks( block.innerBlocks, result ) // Recursively process nested blocks
				return
			}

			const serializedBlock = serialize( block )

			// Remove block placeholder and tags to check if it has a content.
			const cleanedBlock = trim( serializedBlock.replace( /<!--.*?-->/gs, '' ).replace( /<[^>]*>/g, '' ) )
			if ( ! cleanedBlock ) {
				return
			}

			result[ block.clientId ] = serializedBlock.replace( /<!--.*?-->/gs, '' )
		} )

		return result
	}

	return processBlocks( data )
}

/**
 * The `FixAISnackBar` component is used to display a Snackbar notification.
 *
 * @param {Object} props The properties passed to the component.
 */
const FixAISnackBar = ( props ) => {
	const { id, previousResults, isSnippetEditorOpen, isContentTest, reject, removeSnackBar } = props
	const [ index, setIndex ] = useState( previousResults.length )
	const [ isUndo, setUndo ] = useState( false )
	const [ isLoading, setLoading ] = useState( false )
	const [ hasValidBlocks, setHasValidBlocks ] = useState( true )
	const isError = ! isBoolean( isLoading ) // When API returns an error, we store the error message in isLoading state.

	// Sends a request to the API to generate the result for the specified test.
	const fixTest = () => {
		setHasValidBlocks( true )
		const contentBlocks = applyFilters( 'rank_math_content_ai_payload_blocks', props.content_blocks )
		if ( ! Object.keys( contentBlocks ).length ) {
			setHasValidBlocks( false )
			return
		}
		const allowPostContent = includes( [ 'keywordInTitle', 'titleStartWithKeyword', 'titleSentiment', 'titleHasNumber', 'titleHasPowerWords', 'keywordInPermalink', 'lengthContent', 'keywordDensity' ], id )
		const allowBlocks = includes( [ 'keywordIn10Percent', 'keywordInContent', 'lengthContent', 'keywordInSubheadings', 'keywordDensity', 'contentHasShortParagraphs' ], id )
		const replaceText = '<!-- SCRIPT REMOVED -->'
		setLoading( true )

		getData( 'FIX_SEO_TESTS', {
			test_type: id,
			focus_keyword: props.keyword,
			post_title: props.title,
			post_seo_title: props.seo_title,
			post_seo_description: props.description,
			post_permalink: props.url,
			post_content: allowPostContent ? stripScripts( props.content, replaceText ) : null,
			content_blocks: allowBlocks ? mapValues( contentBlocks, ( item ) => stripScripts( item, replaceText ) ) : null,
			post_excerpt: stripScripts( props.excerpt, replaceText ),
			previous_results: previousResults,
		}, ( result ) => {
			if ( ! isEmpty( result.error ) ) {
				setLoading( result.error )
				return
			}
			setLoading( false )
			result = result[ 0 ]
			updateTest( result, props, false )
			setIndex( previousResults.length )
		} )
	}

	/**
	 * Updates the pagination state when the Next or Previous button is clicked.
	 *
	 * @param {number} value The current index representing the page to be set.
	 */
	const setPagination = ( value ) => {
		updateTest( previousResults[ value - 1 ], props, true )
		setIndex( value )
		setUndo( false )
	}

	/**
	 * Triggers an API call immediately after the component is rendered.
	 * Ensures data is fetched and ready for use in the component's initial state.
	 */
	useEffect( () => {
		fixTest()
	}, [] )

	return (
		<div>
			{
				isLoading === true &&
				<div className="snackbar-loader">
					<span></span>
					<span></span>
					<span></span>
					<span></span>
				</div>
			}
			{
				( ! isLoading || isError ) &&
				<div className="snackbar-content">
					<div className="snackbar-header">
						<h2 className="main-title">
							{ __( 'Content AI', 'rank-math' ) }
							&nbsp;<span>&bull;</span>&nbsp;
							{ isSnippetEditorOpen ? __( 'Snippet Editor', 'rank-math' ) : __( 'Post Editor', 'rank-math' ) }
						</h2>

						<Button
							size="small"
							className="close-snackbar"
							onClick={ () => ( removeSnackBar( true ) ) }
							icon={ close }
							label={ __( 'Close', 'rank-math' ) }
						/>
					</div>
					<div className="snackbar-body">
						<div className="pagination-container">
							<h4>
								{ sparkleIcon }
								{ __( 'Generated Content', 'rank-math' ) }
							</h4>

							{
								previousResults.length > 1 &&
								<div className="snackbar-pagination">
									{
										index > 1 &&
										<Button
											size="small"
											className="previous"
											onClick={ () => ( setPagination( index - 1 ) ) }
											label={ __( 'Previous', 'rank-math' ) }
										/>
									}
									<span>{ ( index ) } / { previousResults.length }</span>
									{
										index < previousResults.length &&
										<Button
											size="small"
											className="next"
											onClick={ () => ( setPagination( index + 1 ) ) }
											label={ __( 'Next', 'rank-math' ) }
										/>
									}
								</div>
							}
						</div>

						<p className="desc">
							{ isError && isLoading }
							{
								! isError && (
									hasValidBlocks ? __( 'The highlighted content has been generated by AI for your review. Carefully evaluate the suggested changes and use the options below to accept or reject them.', 'rank-math' )
										: __( 'No valid content blocks found. Please add some content blocks to the post.', 'rank-math' )
								)
							}
						</p>

						{
							! isError && hasValidBlocks &&
							<div className="snackbar-actions">
								<Button
									variant="secondary"
									className="approve-content"
									onClick={ () => {
										if ( isContentTest ) {
											doAction( 'rank_math_content_test_approve', props.content )

											if ( [ 'gutenberg', 'classic' ].includes( rankMath.currentEditor ) ) {
												removeHighlighting( props.content )
											}
										}
										removeSnackBar()
									} }
								>
									{ approveIcon }
									{ __( 'Approve', 'rank-math' ) }
								</Button>
								<Button
									variant="secondary"
									className="regenerate-content"
									onClick={ () => {
										// Reset the content to its original state so we can highlight the changes again.
										if ( isContentTest ) {
											updateTest( '', props, true )
										}

										fixTest()
									} }
								>
									{ regenerateIcon }
									{ __( 'Regenerate', 'rank-math' ) }
								</Button>
								{
									! isUndo &&
									<Button
										variant="secondary"
										className="reject-content"
										onClick={ () => {
											reject()
											setUndo( true )
										} }
									>
										{ rejectIcon }
										{ __( 'Reject', 'rank-math' ) }
									</Button>
								}
								{
									isUndo &&
									<Button
										variant="secondary"
										className="undo-content"
										onClick={ () => {
											reject( true )
											setUndo( false )
										} }
									>
										{ undoIcon }
										{ __( 'Undo', 'rank-math' ) }
									</Button>
								}
							</div>
						}
					</div>
				</div>
			}
		</div>
	)
}

export default compose(
	withSelect( ( select, { id, data, seoTitle, seoDescription, keyword, blocks, originalContent } ) => {
		const previousResults = select( 'rank-math-content-ai' ).getPreviousResults()
		return {
			id,
			keyword,
			title: data.title,
			seo_title: seoTitle,
			url: data.slug,
			description: seoDescription,
			excerpt: ! isEmpty( data.excerpt ) ? data.excerpt : '',
			content: isGutenbergAvailable() ? select( 'core/editor' ).getEditedPostContent() : data.content,
			blocks,
			originalContent,
			content_blocks: isGutenbergAvailable() ? getFlatBlocks( blocks ) : blocks,
			previousResults,
			isSnippetEditorOpen: select( 'rank-math' ).isSnippetEditorOpen(),
			isContentTest: includes( [ 'keywordIn10Percent', 'keywordInContent', 'lengthContent', 'keywordInSubheadings', 'keywordInImageAlt', 'keywordDensity', 'contentHasShortParagraphs' ], id ),
		}
	} ),
	withDispatch( ( _dispatch, props ) => {
		return {
			/**
			 * Resets the test value to its original value.
			 * This function can also handle undo actions if the value was previously rejected.
			 *
			 * @param {boolean} isUndo Indicates whether the function was called to undo a previously rejected value.
			 */
			reject( isUndo = false ) {
				const previousResults = props.previousResults
				if ( isUndo ) {
					updateTest( previousResults[ 0 ], props, true )
					return
				}

				updateTest( '', props, true )
			},

			/**
			 * Removes the `FixAISnackBar` component from the DOM body.
			 * Optionally resets the test value before removing the Snackbar.
			 *
			 * @param {boolean} resetTest Indicates whether to reset the test value before removing the Snackbar.
			 */
			removeSnackBar( resetTest = false ) {
				if ( resetTest ) {
					updateTest( '', props, true )
				}

				if ( props.isContentTest ) {
					removeDataBlockID()
				}

				_dispatch( 'rank-math-content-ai' ).updatePreviousResults( [] )
				document.getElementById( 'rank-math-content-ai-snackbar' ).remove()
			},
		}
	} )
)( FixAISnackBar )
