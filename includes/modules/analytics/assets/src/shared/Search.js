/**
 * WordPress dependencies
 */
import { withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { withRouter } from '../functions'

const Search = () => null

export default withRouter( withFilters( 'rankMath.analytics.searchForm' )( Search ) )
