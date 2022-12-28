/* global confirm */
/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { forEach, difference } from 'lodash'
import { Analyzer, Paper, ResultManager, Helpers } from '@rankMath/analyzer'
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
		let totalPosts = ! $( '#update_all_posts' ).length || $( '#update_all_posts' ).is( ':checked' ) ? rankMath.totalPosts : rankMath.totalPostsWithoutScore
		let progress = totalPosts ? 100 / totalPosts : 0
		const postIds = []
		let count = 0
		function updateSeoScore( response, $this ) {
			let postScores = {}
			$this.addClass( 'disabled' )
			$( '#update_all_scores' ).prop( 'disabled', true )
			if ( response === 'complete' ) {
				$( '.progress-bar span' ).css( 'width', '100%' )
				$( '.count span.update-posts-done' ).text( totalPosts )
				$( '.rank-math-modal-footer' ).removeClass( 'hidden' )
				return
			}

			return new Promise( ( resolve ) => {
				forEach( response, ( data, postID ) => {
					if ( postIds.indexOf( postID ) !== -1 ) {
						return
					}

					const keyword = Helpers.removeDiacritics( data.keyword )

					postIds.push( postID )
					const resultManager = new ResultManager()
					const i18n = wp.i18n
					const paper = new Paper()
					paper.setTitle( data.title )
					paper.setDescription( data.description )
					paper.setText( data.content )
					paper.setKeyword( keyword )
					paper.setKeywords( data.keywords )
					paper.setPermalink( data.url )
					paper.setUrl( data.url )
					if ( data.thumbnail ) {
						paper.setThumbnail( data.thumbnail )
						paper.setThumbnailAltText( data.thumbnailAlt )
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

						let score = resultManager.getScore( paper.getKeyword() )
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
							$( '.count span.update-posts-done' ).text( count )
							if ( count >= totalPosts ) {
								$( '.progress-bar span' ).css( 'width', '100%' )
								$( '.count span.update-posts-done' ).text( totalPosts )
								$( '.rank-math-modal-footer' ).removeClass( 'hidden' )
							} else {
								const data = $this.data( 'args' ) || {}
								data.offset = count
								$this.data( 'args', data )
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

		const scoreUpdaterRow = $( 'tr.update_seo_score' )
		if ( scoreUpdaterRow.length ) {
			// Add checkbox inside the td.
			const label = __( 'Include posts/pages where the score is already set', 'rank-math' )
			if ( rankMath.totalPostsWithoutScore > 0 ) {
				scoreUpdaterRow.find( 'td' ).append( `<div class="update_all_scores"><label><input type="checkbox" name="update_all_scores" id="update_all_scores" value="1" checked="checked" /> ${label}</label></div>` )
			}

			$( '#update_all_scores' ).on( 'change', function() {
				const $this = $( this )
				const button = scoreUpdaterRow.find( 'a.button' )
				const data = button.data( 'args' ) || {}
				data.update_all_scores = $this.is( ':checked' ) ? 1 : 0
				button.data( 'args', data )

				totalPosts = $this.is( ':checked' ) ? rankMath.totalPosts : rankMath.totalPostsWithoutScore
				progress = 100 / totalPosts
				$( '.count span.update-posts-total' ).text( totalPosts )
			} ).trigger( 'change' )
		}
	} )
}( jQuery ) )
