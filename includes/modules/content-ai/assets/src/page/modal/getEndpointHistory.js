/**
 * External dependencies
 */
import { map, compact } from 'lodash'

export default ( endpoint ) => {
	return compact(
		map(
			rankMath.contentAIHistory,
			( value ) => {
				if ( value.key === endpoint ) {
					return value.output
				}
			}
		)
	)
}
