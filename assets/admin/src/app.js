/**
 * WordPress dependencies
 */
import { addAction, addFilter } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import App from './sidebar/App'

addAction( 'rank_math_loaded', 'rank-math', () => {
	addFilter(
		'rank_math_app',
		'rank-math',
		() => {
			return App
		}
	)
} )
