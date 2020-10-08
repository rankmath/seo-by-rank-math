/**
 * External dependencies
 */
import { map, uniqueId, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { getSnippetIcon } from '@helpers/snippetIcon'

const correctSchemaName = ( schema ) => {
	if ( 'BlogPosting' === schema || 'NewsArticle' === schema ) {
		return __( 'Article', 'rank-math' )
	}

	if ( 'WooCommerceProduct' === schema || 'EDDProduct' === schema ) {
		return __( 'Product', 'rank-math' )
	}

	if ( schema.includes( 'Event' ) ) {
		return __( 'Event', 'rank-math' )
	}

	if ( 'MusicGroup' === schema || 'MusicAlbum' === schema ) {
		return __( 'Music', 'rank-math' )
	}

	return schema
}

const SchemaListing = ( { schemas } ) => {
	if ( isUndefined( schemas ) ) {
		return null
	}

	if ( 0 === schemas.length ) {
		return (
			<div className="schema-listing">
				<div className="schema-item">
					<i className={ getSnippetIcon( 'off' ) } />{ ' ' }
					{ __( 'None', 'rank-math' ) }
				</div>
			</div>
		)
	}

	schemas = schemas.split( ', ' )

	return (
		<div className="schema-listing">
			{ map( schemas, ( schema ) => {
				const icon = schema.replace( / /g, '' )
				return (
					<div className="schema-item" key={ uniqueId( 'schema-' ) }>
						<i className={ getSnippetIcon( icon ) } /> { correctSchemaName( schema ) }
					</div>
				)
			} ) }
		</div>
	)
}

export default SchemaListing
