/**
 * External dependencies
 */
import { includes, get, isEmpty } from 'lodash'
import { __ } from '@wordpress/i18n'

/**
 * WordPress dependencies
 */
import { Dashicon } from '@wordpress/components'
import { withSelect } from '@wordpress/data'

const GetStars = ( max, which ) => {
	const content = []
	for ( let i = 1; i <= max; i++ ) {
		content.push(
			<Dashicon
				key={ Math.random() }
				size="13"
				icon={ 'star-' + which }
			/>
		)
	}

	return content
}

const RenderStars = ( rating, min, max ) => {
	rating = rating * ( 100 / max )

	return (
		<div className="serp-result" style={ { width: rating + '%' } }>
			{ GetStars( max, 'filled' ) }
		</div>
	)
}

const RatingPreview = ( { type, data } ) => {
	if (
		! includes(
			[ 'book', 'course', 'event', 'product', 'recipe', 'software' ],
			type
		)
	) {
		return null
	}

	const rating = get( data, type + 'Rating' )
	if ( isEmpty( rating ) ) {
		return null
	}

	const max = get( data, type + 'RatingMax' )
	const min = get( data, type + 'RatingMin' )

	return (
		<div className="rank-math-rating-preview">
			<div className="serp-ratings">
				{ GetStars( max, 'filled' ) }
				{ RenderStars( rating, min, max ) }
			</div>
			<span className="serp-rating-label">
				{ __( 'Rating: ', 'rank-math' ) }
			</span>
			<span className="serp-rating-value">{ rating }</span>
		</div>
	)
}

export default withSelect( ( select ) => {
	const data = select( 'rank-math' ).getRichSnippets()

	return {
		type: data.snippetType,
		data,
	}
} )( RatingPreview )
