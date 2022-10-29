/* global alert, confirm */
/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { forEach, difference } from 'lodash'
import { Analyzer, Paper, ResultManager } from '@rankMath/analyzer'
import { __ } from '@wordpress/i18n'

/*!
 * Rank Math - Status & Tools
 *
 * @version 1.0.33
 * @author  Rank Math
 */

( function( $ ) {
	'use strict'
	// Document Ready
	$( function() {
		const after = $( '.rank-math-tab-nav' )

		function addNotice( msg, which, fadeout = 5000 ) {
			which = which || 'error'
			const notice = $( '<div class="notice is-dismissible rank-math-tool-notice"><p></p></div>' )

			notice
				.hide()
				.addClass( 'notice-' + which )
				.find( 'p' ).text( msg )

			after.prev( '.notice' ).remove()
			after.before( notice )
			notice.slideDown()
			$( 'html,body' ).animate(
				{
					scrollTop: notice.offset().top - 50,
				},
				'slow'
			)
			$( document ).trigger( 'wp-updates-notice-added' )
			if ( fadeout ) {
				setTimeout( function() {
					notice.fadeOut()
				}, fadeout )
			}
		}

		function getResearchesTests( data ) {
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

			return tests
		}

		let updatedPosts = 0
		let width = 0
		const batchSize = rankMath.batchSize
		const progress = 100 / rankMath.totalPosts
		const postIds = []
		let count = 0
		function updateSeoScore( response, $this ) {
			let postScores = {}
			$this.addClass( 'disabled' )
			return new Promise( ( resolve ) => {
				forEach( response, ( data, postID ) => {
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
					if ( data.thumbnail ) {
						paper.setThumbnail( data.thumbnail )
					}
					paper.setContentAI( data.hasContentAi )

					const researches = getResearchesTests( data )
					const analyzer = new Analyzer( { i18n, analysis: researches } )
					analyzer.analyzeSome( researches, paper ).then( function( results ) {

						resultManager.update(
							paper.getKeyword(),
							results,
							true
						)

						let score = resultManager.getScore( data.keyword )
						if ( data.isProduct ) {
							score = data.isReviewEnabled ? score + 1 : score
							score = data.hasProductSchema ? score + 1 : score
						}

						postScores[ postID ] = score
					} )

					updatedPosts = updatedPosts + 1
				} )

				resolve()
			} ).then( () => {
				$.ajax( {
					url: rankMath.api.root + 'rankmath/v1/updateSeoScore',
					method: 'POST',
					beforeSend( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', rankMath.restNonce )
					},
					data: {
						action: 'rank_math_update_seo_score',
						postScores,
					},
					success: function( response ) {
						if ( response == 1 ) {
							count = count + batchSize
							width = width + ( progress * batchSize )
							$( '.progress-bar span' ).css( 'width', width + '%' )
							$( '.count span' ).text( count )
							if ( count >= rankMath.totalPosts ) {
								$( '.progress-bar span' ).css( 'width', '100%' )
								$( '.count span' ).text( rankMath.totalPosts )
								$( '.rank-math-modal-footer' ).removeClass( 'hidden' )
							} else {
								$this.data( 'args', {
									offset: count,
								} )
								runToolsAction( $this )
							}
						}
					},
					error: function( response ) {
						$( '.rank-math-modal-footer' ).removeClass( 'hidden' ).find('p').text( __( 'Something went wrong. Please refresh the page and try again.', 'rank-math' ) )
					},
				} )
			} )
		}

		function runToolsAction( $this ) {
			const action = $this.data( 'action' ),
				args = $this.data( 'args' )

			$.ajax( {
				url: rankMath.api.root + 'rankmath/v1/toolsAction',
				method: 'POST',
				beforeSend( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', rankMath.restNonce )
				},
				data: {
					action,
					args,
				},
			} )
				.always( function() {
					$this.removeAttr( 'disabled' )
				} )
				.fail( function( response ) {
					if ( response ) {
						if (
							response.responseJSON &&
							response.responseJSON.message
						) {
							addNotice( response.responseJSON.message )
						} else {
							addNotice( response.statusText )
						}
					}
				} )
				.done( function( response ) {
					if ( response ) {
						if ( action === 'update_seo_score' ) {
							updateSeoScore( response, $this )
							return
						}

						if ( typeof response === 'string' ) {
							addNotice( response, 'success', false )
							return
						} else if ( typeof response === 'object' && response.status && response.message ) {
							addNotice( response.message, response.status, false )
							return
						}
					}

					addNotice( __( 'Something went wrong. Please try again later.', 'rank-math' ) )
				} )
		}

		$( '.tools-action' ).on( 'click', function( event ) {
			event.preventDefault()

			const $this = $( this )
			if (
				$this.data( 'confirm' ) &&
				! confirm( $this.data( 'confirm' ) )
			) {
				return false
			}

			$this.attr( 'disabled', 'disabled' )
			if ( $this.data( 'action' ) === 'update_seo_score' ) {
				$( '.rank-math-modal-update-score' ).addClass( 'show' )
			}

			runToolsAction( $this )

			return false
		} )

		$( '.rank-math-modal .rank-math-modal-close' ).on( 'click', function() {
			if ( typeof rankMath.startUpdateScore !== 'undefined' && rankMath.startUpdateScore ) {
				window.close()
				return true
			}

			$( this ).closest( '.rank-math-modal' ).css( 'display', 'none' )
		} )

		if ( typeof rankMath.startUpdateScore !== 'undefined' && rankMath.startUpdateScore ) {
			$( '.tools-action[data-action="update_seo_score"]' ).trigger( 'click' )
		}
	} )
}( jQuery ) )
