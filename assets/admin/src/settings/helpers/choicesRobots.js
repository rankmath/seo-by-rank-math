/**
 * External dependencies
 */
import { entries, map } from 'lodash'

export default map( entries( rankMath.choicesRobots ), ( [ id, label ] ) => ( {
	id,
	label,
} ) )
