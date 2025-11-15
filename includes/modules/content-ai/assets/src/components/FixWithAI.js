/**
 * External dependencies
 */
import { isEmpty, toLower, includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'
import { createRoot } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import FixAISnackBar from './FixAISnackBar'
import hasError from '../helpers/hasError'
import showCTABox from '@helpers/showCTABox'
import isTinymceActive from '@helpers/isTinymceActive'
import { contentAIIcon } from '../helpers/icons'
import { getTinyMceBlocks, getContent } from '../helpers/tinymceUtils'

/**
 * Get all Gutenberg blocks as a separate array element.
 *
 * @param {Object} blocks Content blocks.
 */
const getBlocks = ( blocks ) => {
	if ( isEmpty( blocks ) ) {
		return blocks
	}

	return blocks.flatMap( ( block ) => {
		return [ block, ...getBlocks( block.innerBlocks || [] ) ]
	} )
}

const getOriginalBlocks = () => {
	if ( rankMath.currentEditor === 'classic' ) {
		return getTinyMceBlocks()
	}

	if ( rankMath.currentEditor === 'gutenberg' ) {
		return getBlocks( wp.data.select( 'core/block-editor' ).getBlocks() )
	}

	return applyFilters( 'rank_math_content_ai_original_blocks', [] )
}

/**
 * Adds a Snackbar to the document body.
 *
 * This function creates a Snackbar element using the provided `id` and `paper` data.
 * It appends the Snackbar to the body of the document for display.
 *
 * @param {string} id Test ID
 */
const addSnackBarWrapper = ( id ) => {
	const elemDiv = document.createElement( 'div' )
	elemDiv.id = 'rank-math-content-ai-snackbar'
	document.body.appendChild( elemDiv )

	const data = rankMathEditor.assessor.dataCollector.getData()
	const store = wp.data.select( 'rank-math' )
	const keywords = store.getKeywords().split( ',' )

	const isClassicEditor = rankMath.currentEditor === 'classic'

	createRoot(
		document.getElementById( elemDiv.id )
	).render(
		<FixAISnackBar
			id={ id }
			seoTitle={ store.getSerpTitle() }
			seoDescription={ store.getSerpDescription() }
			keyword={ keywords[ 0 ] }
			data={ data }
			blocks={ getOriginalBlocks() }
			originalContent={ isClassicEditor ? getContent() : '' }
		/>
	)
}

/**
 * Determines whether the "Fix With AI" button should be added for a test.
 *
 * @param {string} id     Test ID.
 * @param {Object} result Result of the test. If the test has passed.
 *
 * @return {boolean} - Returns `true` if the "Fix With AI" button should be added, i.e., the test has failed
 *                     and the test is of a specific type. Otherwise, returns `false`.
 */
const canAddFixWithAI = ( id, result ) => {
	// Early Bail if the test has a score.
	if ( result.hasScore() || 'post' !== rankMath.objectType ) {
		return false
	}

	const contentTests = [
		'keywordIn10Percent',
		'keywordInContent',
		'lengthContent',
		'keywordInSubheadings',
		'keywordDensity',
		'contentHasShortParagraphs',
	]

	// Do not add the Fix With AI button to Content tests on Elementor & Divi edtiors.
	if (
		(
			includes( contentTests, id ) &&
			! includes( [ 'gutenberg', 'classic', 'elementor' ], rankMath.currentEditor )
		) ||
		(
			includes( contentTests, id ) &&
			rankMath.currentEditor === 'classic' &&
			tinymce.editors.some( ( editor ) => editor.id === 'content' )
		)
	) {
		return false
	}

	const tests = [
		'keywordInTitle',
		'keywordInMetaDescription',
		'keywordInPermalink',
		'titleStartWithKeyword',
		'titleSentiment',
		'titleHasNumber',
		'titleHasPowerWords',
	]

	return includes( tests, id ) || includes( contentTests, id )
}

/**
 * The `FixWithAI` component renders a `Fix With AI` button for the Content Analysis test.
 * This button allows users to invoke AI-driven functionality to address issues in the test.
 *
 * @param {string} id Test ID.
 */
export default ( { id, result } ) => {
	if ( ! canAddFixWithAI( id, result ) ) {
		return null
	}

	const paper = rankMathEditor.assessor.analyzer.researcher.paper
	const keyword = paper.getKeyword()

	// Do not add the Fix With AI button to keyword tests if a keyword has not been added.
	if ( isEmpty( keyword ) && includes( toLower( id ), 'keyword' ) ) {
		return null
	}

	return (
		<Button
			variant="link"
			className="rank-math-ai-button"
			showTooltip={ true }
			tooltipPosition="top"
			label={ __( 'Fix with AI', 'rank-math' ) }
			onClick={ () => {
				if ( hasError() ) {
					showCTABox( { showProNotice: false } )
					return
				}

				addSnackBarWrapper( id, paper )
			} }
		>
			{ contentAIIcon }
			{ __( 'Fix with AI', 'rank-math' ) }
		</Button>
	)
}
