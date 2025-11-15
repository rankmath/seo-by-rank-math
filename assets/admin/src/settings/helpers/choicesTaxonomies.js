/**
 * External dependencies
 */
import { entries, map } from 'lodash'

export default map(
	entries( rankMath.choices.accessibleTaxonomies ),
	( [ id, obj ] ) => ( { id, label: obj.label } )
)
