import App from './sidebar/App'

import { addAction, addFilter } from '@wordpress/hooks'

addAction( 'rank_math_loaded', 'rank-math', () => {
	addFilter(
		'rank_math_app',
		'rank-math',
		() => {
			return App
		}
	)
} )
