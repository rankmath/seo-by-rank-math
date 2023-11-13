/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * Internal dependencies
 */
import getLastParagraph from '../helpers/getLastParagraph'

/**
 * Function to get the attributes to use in the Write tab.
 *
 * @param {string} instructions Text passed from the Command shortcode.
 */
export default ( instructions ) => {
	const contentAiAttributes = wp.data.select( 'rank-math-content-ai' ).getContentAiAttributes()
	if ( ! instructions ) {
		instructions = ! isEmpty( contentAiAttributes.instructions ) ? contentAiAttributes.instructions : ''
	}
	return {
		document_title: typeof rankMathEditor !== 'undefined' ? rankMathEditor.assessor.dataCollector.getData().title : '',
		text: getLastParagraph(),
		instructions,
		tone: ! isEmpty( contentAiAttributes.tone ) ? contentAiAttributes.tone : rankMath.ca_tone,
		focus_keyword: ! isEmpty( contentAiAttributes.focus_keyword ) ? contentAiAttributes.focus_keyword : '',
		length: ! isEmpty( contentAiAttributes.length ) ? contentAiAttributes.length : 'medium',
		choices: 1,
	}
}
