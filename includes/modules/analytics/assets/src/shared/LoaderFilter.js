/**
 * External dependencies
 */
import { times as loop } from 'lodash'
import ContentLoader from 'react-content-loader'

const LoaderFilter = ( { times = 4, height = '62', className } ) => {
	return (
		<div className={ className }>
			{ loop( times, ( i ) => (
				<button className="components-button" key={ i }>
					<ContentLoader
						animate={ false }
						backgroundColor="#f0f2f4"
						foregroundColor="#f0f2f4"
						style={ { width: '100%', height: height + 'px' } }
					>
						<rect
							x="0"
							y="0"
							rx="0"
							ry="0"
							width="100%"
							height="100%"
						/>
					</ContentLoader>
				</button>
			) ) }
		</div>
	)
}

export default LoaderFilter
