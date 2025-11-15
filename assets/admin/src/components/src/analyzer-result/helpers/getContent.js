/**
 * External Dependencies
 */
import {
	includes,
	map,
	isString,
	max,
	round,
	join,
	isArray,
	entries,
	values,
	isNumber,
} from 'lodash'

/**
 * Get tag cloud HTML.
 *
 * @param {Object} data
 */
const getTagCloud = ( data ) => {
	const fontSizeMax = 22
	const fontSizeMin = 10

	const maxValue = max( values( data ) )

	return (
		<div className="wp-tag-cloud">
			{ map( entries( data ), ( [ keyword, occurrences ], index ) => {
				let size =
					( ( occurrences / maxValue ) * ( fontSizeMax - fontSizeMin ) ) + fontSizeMin
				size = round( size, 2 )

				return (
					<span
						key={ index }
						className="keyword-cloud-item"
						style={ { fontSize: `${ size }px` } }
					>
						{ keyword }
					</span>
				)
			} ) }
		</div>
	)
}

/**
 * Check if result data should be rendered with reversed heading or not.
 *
 * @param {*} id
 */
const isReverseHeading = ( id ) => {
	return includes( [ 'links_ratio', 'keywords_meta', 'page_objects' ], id )
}

/**
 * Check if result data should be rendered as a list or not.
 *
 * @param {string} id
 */
const isList = ( id ) => {
	return includes(
		[
			'img_alt',
			'minify_css',
			'minify_js',
			'active_plugins',
			'h1_heading',
			'h2_headings',
		],
		id
	)
}

/**
 * Render results list.
 *
 * @param {string} id
 * @param {Object} data
 */
const theList = ( id, data ) => {
	return (
		<ul className="info-list">
			{ map( entries( data ), ( [ label, text ], index ) => {
				text = isArray( text ) ? join( text, ', ' ) : text

				if ( isReverseHeading( id ) ) {
					return (
						<li key={ index }>
							<strong>{ label }: </strong>
							{ text }
						</li>
					)
				}

				return (
					<li key={ index }>
						{ ( isString( label ) && ! isNumber( Number( label ) ) ) ? (
							<>
								{ label } ({ text })
							</>
						) : (
							text
						) }
					</li>
				)
			} ) }
		</ul>
	)
}

/**
 * Output test data.
 *
 * @param {Object} result
 */
export default ( result ) => {
	const { data, test_id: id } = result

	if ( 'common_keywords' === id ) {
		return getTagCloud( data )
	}

	if ( isList( id ) || isReverseHeading( id ) ) {
		return theList( id, data )
	}

	if ( includes( [ 'title_length', 'description_length', 'canonical' ], id ) ) {
		const displayData = isArray( data ) ? join( data, ', ' ) : data

		return <code className="full-width">{ displayData }</code>
	}
}
