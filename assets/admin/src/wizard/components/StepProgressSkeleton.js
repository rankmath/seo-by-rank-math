/**
 * Internal Dependencies
 */
import { map } from 'lodash'

/**
 * Step Progress Skeleton
 */
export default () => {
	return (
		<div className="wrapper">
			<div className="main-content steps-progress-skeleton">
				<header>
					<span className="title" />
					<span className="sub-title" />
				</header>

				<div id="field-metabox-rank-math">
					{ map( Array.from( { length: 5 } ), ( _, index ) => (
						<div key={ index } className="field-row">
							<span className="top" />
							<span className="bottom" />
						</div>
					) ) }
				</div>
			</div>
		</div>
	)
}
