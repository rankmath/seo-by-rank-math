/**
 * External dependencies
 */
import { map, uniqueId, isUndefined, lowerCase } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { getSnippetIcon } from '@helpers/snippetIcon'

const SchemaListing = ( { schemas } ) => {
	if ( isUndefined( schemas ) ) {
		return null
	}

	schemas = schemas ? schemas : __( 'None', 'rank-math-pro' )
	schemas = schemas.toString().split( ', ' )

	return (
		<div className="schema-listing">
			{ map( schemas, ( schema ) => {
				const icon = schema.replace( / /g, '' )
				const className = 'schema-item ' + lowerCase( icon )
				return (
					<div className={ className } key={ uniqueId( 'schema-' ) }>
						<i className={ getSnippetIcon( icon ) } /> { schema }
					</div>
				)
			} ) }
		</div>
	)
}

export default SchemaListing
