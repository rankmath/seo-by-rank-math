/**
 * External Dependencies
 */
import { map } from 'lodash'

/**
 * Internal Dependencies
 */
import { helpItems, links } from './utils'

export default () => (
	<div className="two-col rank-math-box-help">
		{ map( helpItems, ( { heading, items }, index ) => (
			<div key={ index } className="col rank-math-box">
				<header>
					<h3>{ heading }</h3>
				</header>

				<div className="rank-math-box-content">
					<ul className="rank-math-list-icon">
						{ map( items, ( { id, icon, title, description } ) => (
							<li key={ id }>
								<a target="_blank" rel="noreferrer" href={ links[ id ] ?? '' }>
									<i className={ `rm-icon rm-icon-${ icon }` }></i>

									<div>
										<strong>{ title }</strong>

										<p>{ description }</p>
									</div>
								</a>
							</li>
						) ) }
					</ul>
				</div>
			</div>
		) ) }
	</div>
)
