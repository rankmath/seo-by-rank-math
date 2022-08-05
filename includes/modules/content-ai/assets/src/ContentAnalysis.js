/**
 * External dependencies
 */
import { ceil, filter, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { addAction, addFilter } from '@wordpress/hooks'
import { select } from '@wordpress/data'

class ContentAnalysis {
	/**
	 * Constructor.
	 */
	constructor() {
		if ( ! rankMath.isUserRegistered ) {
			return
		}

		this.init = this.init.bind( this )
		this.contentLength = this.contentLength.bind( this )
		this.contentLengthBoundary = this.contentLengthBoundary.bind( this )
		this.keywordDensity = this.keywordDensity.bind( this )
		this.removeTests = this.removeTests.bind( this )

		this.researchesTests = rankMath.assessor.researchesTests
		this.ca_keyword = rankMath.ca_keyword.keyword
		addAction( 'rank_math_loaded', 'rank-math', this.init )
		addAction( 'rank_math_keyword_refresh', 'rank-math', this.init )
		addAction( 'rank_math_content_ai_changed', 'rank-math', this.init )
	}

	init( keyword ) {
		if ( keyword ) {
			this.ca_keyword = keyword
		}

		this.keyword = rankMathEditor.assessor.getPrimaryKeyword()
		this.data = select( 'rank-math' ).getKeywordsData()
		if ( isEmpty( this.data ) ) {
			return
		}

		this.filters()
		this.removeTests()

		if ( keyword ) {
			rankMathEditor.refresh( 'content' )
		}
	}

	removeTests() {
		if ( this.ca_keyword !== this.keyword ) {
			rankMath.assessor.researchesTests = this.researchesTests
			return
		}

		const recommendations = this.data.recommendations
		rankMath.assessor.researchesTests = filter( rankMath.assessor.researchesTests, ( test ) => {
			if ( ! recommendations.mediaCount && 'contentHasAssets' === test ) {
				return false
			}

			if ( ! recommendations.linkCount.internal && 'linksHasInternal' === test ) {
				return false
			}

			if (
				! recommendations.linkCount.external &&
				(
					'linksHasExternals' === test ||
					'linksNotAllExternals' === test
				)
			) {
				return false
			}

			return true
		} )
	}

	filters() {
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

		addFilter(
			'rankMath_analysis_keywordDensity',
			'rank-math',
			this.keywordDensity
		)
	}

	/**
	 * Change recommended content length text.
	 *
	 * @param  {Object} data Content Length Text.
	 * @return {Object} Filtered Content Length Text.
	 */
	contentLength( data ) {
		if ( this.ca_keyword !== this.keyword ) {
			return data
		}

		const wordCount = this.data.recommendations.wordCount
		const max = ceil( ( wordCount * 150 ) / 100 )
		return {
			hasScore: data.hasScore,
			// translators: contet length
			failed: sprintf(
				// translators: contet length
				__( 'Content is %1$s words long. Consider using at least %2$s words.', 'rank-math' ),
				'%1$d',
				wordCount,
			),
			tooltipText: sprintf(
				// translators: contet length
				__( 'Minimum recommended content length should be %1$d words.', 'rank-math' ),
				wordCount,
			),
			emptyContent: sprintf(
				// translators: contet length
				__( 'Content should be %1$s long.', 'rank-math' ),
				'<a href="https://s.rankmath.com/100contentlength" target="_blank">' +
					wordCount + ' - ' + max + __( ' words', 'rank-math' ) +
					'</a>'
			),
		}
	}

	/**
	 * Change recommended content length boundaries.
	 *
	 * @param  {Object} boundaries Content Length Boundaries.
	 * @return {Object} Contnt Length Boundaries
	 */
	contentLengthBoundary( boundaries ) {
		if ( this.ca_keyword !== this.keyword ) {
			return boundaries
		}

		const wordCount = this.data.recommendations.wordCount
		const max = ceil( ( wordCount * 150 ) / 100 )
		return {
			recommended: {
				boundary: max,
				score: 8,
			},
			belowRecommended: {
				boundary: ceil( max / 2 ),
				score: 5,
			},
			low: {
				boundary: wordCount,
				score: 2,
			},
		}
	}

	/**
	 * Change Keyword density.
	 *
	 * @param  {number} density Keyword density.
	 * @param  {number} count   Total number of times a keyword appwars in the content.
	 * @return {number} Updated keyword density.
	 */
	keywordDensity( density, count ) {
		if ( isEmpty( this.data.keywords.content ) || isEmpty( this.data.keywords.content[ this.keyword ] ) ) {
			return density
		}

		const data = this.data.keywords.content[ this.keyword ]
		const score = ( count / data.average ) * 100
		if ( score > 80 ) {
			return 1.1
		}

		if ( 100 < score ) {
			return 2.5
		}

		if ( 80 < score ) {
			return 1.1
		}

		if ( 50 < score ) {
			return 0.80
		}

		return 0.5
	}
}

export default ContentAnalysis
