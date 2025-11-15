/**
 * External Dependencies
 */
import { entries, map, compact, isEmpty } from 'lodash'

/**
 * WordPress Dependencies
 */
import { TabPanel } from '@wordpress/components'
import { useState } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal Dependencies
 */
import Result from './Result'
import getTabs from './helpers/getTabs.js'
import getCategoryLabel from './helpers/getCategoryLabel'

const getCategoryResults = ( filter, results ) => {
	return map( results, ( result, resultIndex ) => {
		if ( filter !== 'all' && filter !== result.status ) {
			return null
		}

		return (
			<div
				key={ resultIndex }
				className={ `table-row rank-math-result-status-${ result.status }` }
				data-status={ result.status }
			>
				<Result result={ result } />
			</div>
		)
	} )
}

export default ( results ) => {
	const [ filter, setFilter ] = useState( 'all' )
	return (
		<TabPanel
			className="seo-analysis-results"
			tabs={ getTabs( results.metrices ) }
			initialTabName="all"
			onSelect={ ( tab ) => ( setFilter( tab ) ) }
		>
			{
				() => (
					map( entries( results.results ), ( [ category, categoryResults ], index ) => {
						const result = getCategoryResults( filter, categoryResults )
						if ( isEmpty( compact( result ) ) ) {
							return
						}

						return (
							<div
								key={ index }
								className={ `rank-math-result-table rank-math-result-category-${ category }` }
							>
								<div className="category-title">{ getCategoryLabel( category ) }</div>
								{ applyFilters( 'rank_math_analysis_category_notice', '', category ) }
								{ result }
							</div>
						)
					} )
				)
			}
		</TabPanel>
	)
}
