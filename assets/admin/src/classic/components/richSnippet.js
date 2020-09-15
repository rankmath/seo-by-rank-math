/**
 * External dependencies
 */
import $ from 'jquery'

/**
 * Internal dependencies
 */
import { getSnippetIcon } from '@helpers/snippetIcon'

class richSnippet {
	/**
	 * Class constructor
	 */
	constructor() {
		if ( 'post' !== rankMath.objectType ) {
			return
		}

		const icon = $(
			'.rank-math-tabs-navigation a[href="#setting-panel-schema"] .dashicons'
		)

		const wpaiors = $( '#_bsf_post_type' )
		$(
			'#rank_math_rich_snippet, input[type="radio"][name="rank_math_rich_snippet"]'
		)
			.on( 'change', function() {
				const id = $( this ).attr( 'id' )
				const value =
					'rank_math_rich_snippet' === id
						? $( this ).val()
						: $(
							'input[type="radio"][name="rank_math_rich_snippet"]:checked'
						).val()

				icon.removeClass().addClass( getSnippetIcon( value ) )

				if ( wpaiors.length && 'off' !== value ) {
					wpaiors.val( '0' ).trigger( 'change' )
				}
			} )
			.trigger( 'change' )

		if ( ! wpaiors.length ) {
			return
		}

		const richSnippetSelect = $( '#rank_math_rich_snippet' )
		const productSnippet = $(
			'input[name="rank_math_rich_snippet"][value="off"]'
		)
		wpaiors
			.on( 'change', function() {
				if ( '0' !== wpaiors.val() ) {
					richSnippetSelect.val( 'off' ).trigger( 'change' )
					productSnippet.prop( 'checked', true ).trigger( 'change' )
				}
			} )
			.trigger( 'change' )
	}
}

export default richSnippet
