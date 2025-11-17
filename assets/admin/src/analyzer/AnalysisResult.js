/**
 * External dependencies
 */
import { isUndefined, isNumber } from 'lodash'

/**
 * Analysis result.
 */
class AnalysisResult {
	/**
	 * Class constructor.
	 */
	constructor() {
		this.has = false
		this.score = 0
		this.maxScore = 0
		this.text = ''
		this.empty = ''
		this.tooltip = ''

		return this
	}

	/**
	 * Check if a score is available.
	 *
	 * @return {boolean} Has score or not.
	 */
	hasScore() {
		return this.has
	}

	/**
	 * Get the available score.
	 *
	 * @return {number} Result score.
	 */
	getScore() {
		return this.score
	}

	/**
	 * Set the score for the assessment.
	 *
	 * @param {number} score The score to set for analysis
	 *
	 * @return {AnalysisResult} Class instance for chaining.
	 */
	setScore( score ) {
		if ( isNumber( score ) ) {
			this.score = score
			this.has = 0 < score
		}

		return this
	}

	/**
	 * Set the maximum score for the assessment.
	 *
	 * @param {number} score The maximum score to set for analysis
	 *
	 * @return {AnalysisResult} Class instance for chaining.
	 */
	setMaxScore( score ) {
		if ( isNumber( score ) ) {
			this.maxScore = score
		}

		return this
	}

	/**
	 * Get the maximum score.
	 *
	 * @return {number} Result maximum score.
	 */
	getMaxScore() {
		return this.maxScore
	}

	/**
	 * Check if a text is available.
	 *
	 * @return {boolean} Whether or not a text is available.
	 */
	hasText() {
		return '' !== this.text
	}

	/**
	 * Get the available text.
	 *
	 * @return {string} Return text message.
	 */
	getText() {
		return this.hasText() ? this.text : this.empty
	}

	/**
	 * Set the text for the analysis.
	 *
	 * @param {string} text The text to be used for the text property
	 *
	 * @return {AnalysisResult} Class instance for chaining.
	 */
	setText( text ) {
		this.text = isUndefined( text ) ? '' : text
		return this
	}

	/**
	 * Set the empty for the analysis.
	 *
	 * @param {string} empty The empty to be used for the empty property
	 *
	 * @return {AnalysisResult} Class instance for chaining.
	 */
	setEmpty( empty ) {
		this.empty = isUndefined( empty ) ? '' : empty
		return this
	}

	/**
	 * Check if a tooltip is available.
	 *
	 * @return {boolean} Whether or not a tooltip is available.
	 */
	hasTooltip() {
		return '' !== this.tooltip
	}

	/**
	 * Get the available tooltip.
	 *
	 * @return {string} Result tooltip.
	 */
	getTooltip() {
		return this.tooltip
	}

	/**
	 * Set the tooltip for the analysis.
	 *
	 * @param {string} tooltip The tooltip to be used for the tooltip property
	 *
	 * @return {AnalysisResult} Class instance for chaining.
	 */
	setTooltip( tooltip ) {
		this.tooltip = isUndefined( tooltip ) ? '' : tooltip
		return this
	}
}

export default AnalysisResult
