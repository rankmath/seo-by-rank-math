/**
 * Extenal dependencies
 */
import { withRouter } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { withFilters } from '@wordpress/components'

const Search = () => null

export default withRouter( withFilters( 'rankMath.analytics.searchForm' )( Search ) )
