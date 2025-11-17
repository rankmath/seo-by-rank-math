/**
 * WordPress Dependencies
 */
import { addQueryArgs } from '@wordpress/url'

export default ( page = '', args = {} ) => {
	page = page ? 'rank-math-' + page : 'rank-math'
	args = { ...args, page }

	return addQueryArgs( rankMath.adminurl, args )
}
