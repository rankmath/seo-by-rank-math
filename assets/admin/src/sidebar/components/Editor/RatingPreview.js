/**
 * External dependencies
 */
import { includes, get, isEmpty, find } from 'lodash'
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

const RatingPreview = ( { schema } ) => {
	if ( isEmpty( schema ) ) {
		return null
	}

	const reviewRating = get( schema, 'review.reviewRating', {} )
	const rating = reviewRating.ratingValue
	if ( isEmpty( rating ) ) {
		return null
	}

	const min = get( reviewRating, 'worstRating', 1 )
	const max = get( reviewRating, 'bestRating', 5 )

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
	const schemas = select( 'rank-math' ).getSchemas()
	const found = find( schemas, ( schema ) => {
		return includes( [ 'Book', 'Course', 'Product', 'Recipe', 'SoftwareApplication' ], schema[ '@type' ] )
	} )

	return { schema: found }
} )( RatingPreview )
