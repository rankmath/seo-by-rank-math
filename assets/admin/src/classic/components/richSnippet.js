/**
 * External dependencies
 */
import $ from 'jquery'

class richSnippet {
	/**
	 * Class constructor
	 */
	constructor() {
		if ( 'post' !== rankMath.objectType ) {
			return
		}

		const classHash = {
			off: 'rm-icon-misc',
			article: 'rm-icon-post',
			book: 'rm-icon-book',
			course: 'rm-icon-course',
			event: 'rm-icon-calendar',
			jobposting: 'rm-icon-job',
			local: 'rm-icon-local-seo',
			music: 'rm-icon-music',
			product: 'rm-icon-cart',
			recipe: 'rm-icon-recipe',
			restaurant: 'rm-icon-restaurant',
			video: 'rm-icon-video',
			person: 'rm-icon-users',
			review: 'rm-icon-star',
			service: 'rm-icon-service',
			software: 'rm-icon-software',
		}
		const icon = $(
			'.rank-math-tabs-navigation a[href="#setting-panel-richsnippet"] .dashicons'
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

				icon.removeClass().addClass( 'rm-icon ' + classHash[ value ] )

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
