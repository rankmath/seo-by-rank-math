/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { addFilter } from '@wordpress/hooks'

class CommonFilters {
	constructor() {
		if ( ! rankMath.is_front_page ) {
			return
		}

		addFilter(
			'rankMath_analysis_contentLength',
			'rank-math',
			this.contentLength
		)
		addFilter(
			'rankMath_analysis_contentLength_boundaries',
			'rank-math',
			this.contentLengthBoundary
		)
	}

	/**
	 * Change recommended content length text on homepage.
	 *
	 * @param {Object} data Content Length Text.
	 * @return {Object} Filtered Content Length Text.
	 */
	contentLength( data ) {
		return {
			hasScore: data.hasScore,
			// translators: contet length
			failed: __(
				'Content is %1$d words long. Consider using at least 300 words.',
				'rank-math'
			),
			tooltipText: __(
				'Minimum recommended content length should be 300 words.',
				'rank-math'
			),
			emptyContent: sprintf(
				// translators: contet length
				__( 'Content should be %1$s long.', 'rank-math' ),
				'<a href="https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_campaign=WP#overall-content-length" target="_blank">' +
					__( '300 words', 'rank-math' ) +
					'</a>'
			),
		}
	}

	/**
	 * Change recommended content length boundaries on homepage.
	 *
	 * @return {Object} Contnt Length Boundaries
	 */
	contentLengthBoundary() {
		return {
			recommended: {
				boundary: 299,
				score: 8,
			},
			belowRecommended: {
				boundary: 200,
				score: 5,
			},
			low: {
				boundary: 50,
				score: 2,
			},
		}
	}
}

export default CommonFilters
