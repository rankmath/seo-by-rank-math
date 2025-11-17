/**
 * External dependencies
 */
import { entries, map } from 'lodash'

export default map(
	entries( rankMath.choices.postTypes ),
	( [ id, label ] ) => ( { id, label } )
)
