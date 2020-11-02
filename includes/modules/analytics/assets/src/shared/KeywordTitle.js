/**
 * WordPress dependencies
 */
import { withFilters } from '@wordpress/components'
import { decodeEntities } from '@wordpress/html-entities'

const KeywordTitle = ( { query } ) => {
	return <h4>{ decodeEntities( query ) }</h4>
}

export default withFilters( 'rankMath.analytics.keywordTitle' )( KeywordTitle )
