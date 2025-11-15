/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { forEach, difference } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import { Analyzer, Paper, ResultManager } from '@rankMath/analyzer'
import addLog from './addLog'
import { applyFilters } from '@wordpress/hooks'

const postIds = []
const getResearchesTests = ( data ) => {
	let tests = rankMath.assessor.researchesTests
	tests = difference(
		tests,
		[
			// Unneeded, has no effect on the score.
			'keywordNotUsed',
		]
	)
	if ( ! data.isProduct ) {
		return tests
	}

	tests = difference(
		tests,
		[
			'keywordInSubheadings',
			'linksHasExternals',
			'linksNotAllExternals',
			'linksHasInternal',
			'titleSentiment',
			'titleHasNumber',
			'contentHasTOC',
		]
	)

	return applyFilters( 'rank_math_update_score_researches_tests', tests, data )
}

export default ( postsData, logger, setLogger, callback, start, end, totalPosts ) => {
	const postScores = {}
	if ( postsData === 'complete' ) {
		callback()
		return
	}

	return new Promise( ( resolve ) => {
		forEach( postsData, ( data, postID ) => {
			if ( postIds.indexOf( postID ) !== -1 ) {
				return
			}

			postIds.push( postID )
			const resultManager = new ResultManager()
			const i18n = wp.i18n
			const paper = new Paper()
			paper.setTitle( data.title )
			paper.setDescription( data.description )
			paper.setText( data.content )
			paper.setKeyword( data.keyword )
			paper.setKeywords( data.keywords )
			paper.setPermalink( data.url )
			paper.setUrl( data.url )
			paper.setSchema( data.schemas )
			if ( data.thumbnail ) {
				paper.setThumbnail( data.thumbnail )
			}
			paper.setContentAI( data.hasContentAi )
			const researches = getResearchesTests( data )
			const analyzer = new Analyzer( { i18n, analysis: researches } )
			analyzer.analyzeSome( researches, paper ).then( ( results ) => {
				resultManager.update(
					paper.getKeyword(),
					results,
					true
				)

				let score = resultManager.getScore( paper.getKeyword() )
				if ( data.isProduct ) {
					score = data.isReviewEnabled ? score + 1 : score
					score = data.hasProductSchema ? score + 1 : score
				}

				postScores[ postID ] = score
			} )
		} )

		resolve()
	} ).then( () => {
		jQuery.ajax( {
			url: rankMath.api.root + 'rankmath/v1/updateSeoScore',
			method: 'POST',
			beforeSend( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', rankMath.restNonce )
			},
			data: {
				action: 'rank_math_update_seo_score',
				postScores,
			},
			success: () => {
				addLog(
					sprintf(
						/* translators: 1. Score update start index 2. Score update end index 3. Total posts to calculated the SEO score for. */
						__( 'Calculating SEO score for posts %1$s - %2$s out of %3$s', 'rank-math' ),
						start,
						end,
						totalPosts,
					),
					logger,
					setLogger
				)
				callback()
			},
			error: ( response ) => {
				addLog( response.statusText, logger, setLogger )
			},
		} )
	} )
}
