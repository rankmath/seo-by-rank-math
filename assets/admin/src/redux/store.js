/**
 * WordPress dependencies
 */
import { combineReducers, registerStore } from '@wordpress/data'

/**
 * Internal dependencies
 */
import * as actions from './actions'
import * as reducers from './reducers'
import * as selectors from './selectors'

const store = registerStore( 'rank-math', {
	reducer: combineReducers( reducers ),
	selectors,
	actions,
} )

export function getStore() {
	return store
}
