/**
 * External Dependencies
 */
import { map } from 'lodash'

export default ( { title } ) => {
	return (
		<div className="rank-math-skeleton rank-math-system-status rank-math-ui container">
			<div className="rank-math-box">
				<header>
					<h3>{ title }</h3>
				</header>
				<div className="copy-button-wrapper"></div>
				<div className="rank-math-panel components-panel">
					{ map( Array.from( { length: 8 } ), ( _, index ) => (
						<div key={ index } className="components-panel__body" />
					) ) }
				</div>
			</div>
		</div>
	)
}
