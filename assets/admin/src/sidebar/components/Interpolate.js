/**
 * External dependencies
 */
import { isUndefined } from 'lodash'
import interpolateComponents from 'interpolate-components'

const Interpolate = ( { tags, components, children } ) => {
	components = components || {}

	if ( false === isUndefined( tags ) ) {
		tags = tags.split( ',' )
		tags.forEach( ( tag ) => {
			const CName = tag
			components[ tag ] = <CName />
		} )
	}

	return interpolateComponents( {
		mixedString: children,
		components,
	} )
}

export default Interpolate
