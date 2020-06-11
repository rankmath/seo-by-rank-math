/**
 * External dependencies
 */
import $ from 'jquery'

class PrimaryTerm {
	/**
	 * Class constructor
	 */
	constructor() {
		if ( 'post' !== rankMath.objectType ) {
			return
		}

		$( '[data-primary-term]' ).each( function() {
			const field = $( this )
			const selected = field.val()
			const taxonomy = field.data( 'primary-term' )
			const metaboxTaxonomy = $( '#' + taxonomy + 'div' )
			const taxonomyCheckList = $( '#' + taxonomy + 'checklist' )
			const taxonomyListItem = taxonomyCheckList.find( 'li' )
			const checkedTerms = taxonomyCheckList.find(
				'input[type="checkbox"]:checked'
			)
			taxonomyListItem.addClass( 'rank-math-primary-term-li' )
			taxonomyListItem.find( 'input' ).each( function() {
				const checkbox = $( this )
				const label = checkbox.parent()

				label.append(
					'<span class="rank-math-tooltip"><input class="rank-math-make-primary" value="' +
						checkbox.val() +
						'" type="radio" name="rank_math_primary_' +
						taxonomy +
						'"><span>Make Term Primary</span></span>'
				)
			} )

			checkedTerms.each( function() {
				const term = $( this )
				const listItem = term.closest( 'li' )

				listItem.addClass( 'term-checked' )
				if ( taxonomy + '-' + selected === listItem.attr( 'id' ) ) {
					listItem.addClass( 'term-marked-primary' )
					listItem
						.find( '>label .rank-math-make-primary' )
						.prop( 'checked', true )
				}
			} )

			metaboxTaxonomy.on( 'click', 'input[type="checkbox"]', function() {
				const term = $( this )
				const listItem = term.closest( 'li' )

				listItem.toggleClass( 'term-checked' )
				if (
					1 === taxonomyCheckList.find( 'li.term-checked' ).length ||
					( listItem.hasClass( 'term-marked-primary' ) &&
						! listItem.hasClass( 'term-checked' ) )
				) {
					const first = taxonomyCheckList.find(
						'li.term-checked:first > label .rank-math-make-primary'
					)

					if ( 0 < first.length ) {
						first.trigger( 'click' )
					} else {
						taxonomyListItem.removeClass( 'term-marked-primary' )
						taxonomyListItem
							.find( 'input[type="radio"]' )
							.prop( 'checked', false )
						field.val( '' )
					}
				}
			} )

			metaboxTaxonomy.on( 'click', '.rank-math-make-primary', function() {
				const input = $( this )
				const listItem = input.closest( 'li' )

				taxonomyListItem.removeClass( 'term-marked-primary' )
				listItem.addClass( 'term-marked-primary' )
				field.val( input.val() )
			} )

			taxonomyCheckList.on( 'wpListAddEnd', function() {
				const li = taxonomyCheckList.find(
					'li:not(.rank-math-primary-term-li)'
				)
				li.addClass( 'rank-math-primary-term-li term-checked' )
					.find( 'input' )
					.each( function() {
						const checkbox = $( this )
						const label = checkbox.parent()

						label.append(
							'<span class="rank-math-tooltip"><input class="rank-math-make-primary" value="' +
								checkbox.val() +
								'" type="radio" name="rank_math_primary_' +
								taxonomy +
								'"><span>Make Term Primary</span></span>'
						)
					} )
			} )
		} )
	}
}

export default PrimaryTerm
