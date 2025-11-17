/**
 * External Dependencies
 */
import { isEmpty } from 'lodash'

/**
 * Internal Dependencies
 */
import Graphs from './Graphs'
import DisplayResults from './DisplayResults'

export default ( { results } ) => {
	if ( isEmpty( results ) ) {
		return
	}

	return (
		<div className="rank-math-results-wrapper">
			<Graphs { ...results } />

			<div id="analysis-result">
				<DisplayResults { ...results } />
			</div>
		</div>
	)
}
