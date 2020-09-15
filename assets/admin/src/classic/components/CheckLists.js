/**
 * External dependencies
 */
import jQuery from 'jquery'
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { addAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import getPartialClass from '@helpers/getPartialClass'
import getClassByScore from '@helpers/getClassByScore'

class CheckLists {
	/**
	 * Class constructor
	 */
	constructor() {
		if ( ! rankMath.canUser.analysis ) {
			return
		}

		jQuery( '#setting-panel-general' ).append(
			'<div id="rank-math-serp-checklist" class="rank-math-serp-checklist"></div>'
		)

		this.elem = jQuery( '#rank-math-serp-checklist' )
		this.scoreText = rankMath.showScore
			? '<span class="score-text"><span class="score-icon"><svg viewBox="0 0 460 460" xmlns="http://www.w3.org/2000/svg" width="20"><g><path d="m462 234.84-76.17 3.43 13.43 21-127 81.18-126-52.93-146.26 60.97 10.14 24.34 136.1-56.71 128.57 54 138.69-88.61 13.43 21z"/><path d="m54.1 312.78 92.18-38.41 4.49 1.89v-54.58h-96.67zm210.9-223.57v235.05l7.26 3 89.43-57.05v-181zm-105.44 190.79 96.67 40.62v-165.19h-96.67z"/></g></svg></span> SEO: <strong>Not available</strong></span>'
			: ''
		this.fkScoreText = rankMath.showScore
			? '<span class="score-text">Not available</span>'
			: ''
		// Score in the Publish box.
		this.scoreElem = jQuery(
			'<div class="misc-pub-section rank-math-seo-score loading">' +
				this.scoreText +
				'</div>'
		)
		this.scoreText = this.scoreElem.find( 'strong' )

		// Update Score in score field
		this.scoreField = jQuery( '#rank_math_seo_score' )

		// Score below Focus keyword label.
		this.fkScoreElem = jQuery(
			'<div class="rank-math-seo-score loading below-focus-keyword">' +
				this.fkScoreText +
				'</div>'
		)
		this.fkScoreText = this.fkScoreElem.find( 'span' )

		jQuery( '#misc-publishing-actions' ).append( this.scoreElem )
		jQuery( '.cmb-td', '.cmb2-id-rank-math-focus-keyword' ).append(
			this.fkScoreElem
		)

		this.events()

		this.refreshResults = this.refreshResults.bind( this )
		addAction(
			'rank_math_refresh_results',
			'rank-math',
			this.refreshResults
		)
	}

	events() {
		this.elem.on( 'click', '.group-handle', ( event ) => {
			event.preventDefault()
			const handle = jQuery( event.target ),
				layout = {}

			let group = handle.closest( '.rank-math-serp-group' )
			group.toggleClass( 'state-open state-closed' )

			this.elem.find( '>.rank-math-serp-group' ).each( function() {
				group = jQuery( this )
				layout[ group.data( 'id' ) ] = group.hasClass( 'state-closed' )
					? 'closed'
					: 'open'
			} )

			jQuery.ajax( {
				url: rankMath.ajaxurl,
				type: 'POST',
				data: {
					action: 'rank_math_save_checklist_layout',
					security: rankMath.security,
					layout,
				},
			} )
		} )
	}

	refreshResults() {
		const keyword = rankMathEditor.getSelectedKeyword()
		this.isPrimary = keyword === rankMathEditor.getPrimaryKeyword()
		this.results = rankMathEditor.resultManager.getResult( keyword )

		if ( isUndefined( this.results ) ) {
			return null
		}

		const groupsData = Object.keys( this.getGroups() ).map( ( index ) =>
			this.renderGroup( index )
		)
		this.elem.html( groupsData )

		this.updateScore()
	}

	renderGroup( index ) {
		this.errors = 0
		const listItems = this.renderGroupItems( index )
		const stateClass =
			'basic' === index ||
			jQuery( '#rank-math-serp-group-' + index ).hasClass( 'state-open' )
				? 'state-open'
				: 'state-closed'
		const scoreClass = 0 === this.errors ? 'test-ok' : 'test-fail'
		let statusText = __( 'All Good', 'rank-math' )
		if ( 0 < this.errors ) {
			statusText =
				1 === this.errors
					? this.errors + ' ' + __( 'Error', 'rank-math' )
					: this.errors + ' ' + __( 'Errors', 'rank-math' )
		}

		return `
		<div id="rank-math-serp-group-${ index }" class="rank-math-serp-group ${ stateClass }" data-id="${ index }">
			<div class="group-handle">
				<h4>${ this.getGroupTitle( index ) }</h4>
				<span class="rank-math-group-score ${ scoreClass }">${ statusText }</span>
				<button type="button" class="group-handlediv" aria-expanded="true"><span class="screen-reader-text"></span><span class="toggle-indicator"></span></button>
			</div>
			<ul>${ listItems }</ul>
		</div>`
	}

	renderGroupItems( index ) {
		const results = this.results.results
		const groupItems = this.getGroupItems( index )

		let listData = ''
		/*eslint array-callback-return: 0*/
		Object.keys( groupItems ).map( ( id ) => {
			if (
				isUndefined( results[ id ] ) ||
				( ! this.isPrimary && groupItems[ id ] )
			) {
				return false
			}

			const result = results[ id ]
			let classes = 'seo-check-' + id
			classes += result.hasScore() ? ' test-ok' : ' test-fail'

			if (
				result.hasScore() &&
				[
					'contentHasAssets',
					'lengthContent',
					'keywordDensity',
				].includes( id )
			) {
				classes +=
					' ' +
					getPartialClass( result.getScore(), result.getMaxScore() )
			}

			if ( 'fleschReading' === id && result.hasScore() ) {
				classes += ' ' + result.note.replace( / /g, '-' )
			}

			if ( false === result.hasScore() ) {
				this.errors += 1
			}

			const tooltipText = result.hasTooltip()
				? '<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help" ></em><span>' +
				result.getTooltip() +
				'</span></span>'
				: ''

			listData += `<li key="${ id }" class="${ classes }">
					<span>${ result.getText() }</span>
					${ tooltipText }
				</li>`
		} )

		return listData
	}

	updateScore() {
		const count = rankMathEditor.resultManager.getScore(
			rankMathEditor.getPrimaryKeyword()
		)

		const status = getClassByScore( count )

		this.scoreElem.removeClass( 'loading bad-fk ok-fk good-fk' )
		this.fkScoreElem.removeClass( 'loading bad-fk ok-fk good-fk' )

		this.scoreElem.addClass( status )
		this.fkScoreElem.addClass( status )

		this.scoreText.html( count + ' / 100' )
		this.fkScoreText.html( count + ' / 100' )

		this.scoreField.val( parseInt( count ) )
	}

	getGroupItems( group ) {
		let hash = ''

		if ( 'post' === rankMath.objectType ) {
			hash = {
				basic: {
					keywordInTitle: true,
					keywordInMetaDescription: true,
					keywordInPermalink: true,
					keywordIn10Percent: true,
					keywordInContent: false,
					lengthContent: false,
				},
				advanced: {
					keywordInSubheadings: false,
					keywordInImageAlt: true,
					keywordDensity: false,
					lengthPermalink: false,
					linksHasExternals: false,
					linksNotAllExternals: false,
					linksHasInternal: false,
					keywordNotUsed: true,
				},
				'title-readability': {
					titleStartWithKeyword: true,
					titleSentiment: false,
					titleHasPowerWords: false,
					titleHasNumber: false,
				},
				'content-readability': {
					contentHasTOC: false,
					contentHasShortParagraphs: false,
					contentHasAssets: false,
				},
			}
		} else {
			hash = {
				basic: {
					keywordInTitle: true,
					keywordInMetaDescription: true,
					keywordInPermalink: true,
				},
				advanced: {
					titleStartWithKeyword: true,
					keywordNotUsed: true,
				},
			}
		}

		return hash[ group ]
	}

	getGroupTitle( index ) {
		return this.getGroups()[ index ]
	}

	getGroups() {
		if ( 'post' === rankMath.objectType ) {
			return {
				basic: __( 'Basic SEO', 'rank-math' ),
				advanced: __( 'Additional', 'rank-math' ),
				'title-readability': __( 'Title Readability', 'rank-math' ),
				'content-readability': __( 'Content Readability', 'rank-math' ),
			}
		}

		return {
			basic: __( 'Basic SEO', 'rank-math' ),
			advanced: __( 'Additional', 'rank-math' ),
		}
	}
}

export default CheckLists
