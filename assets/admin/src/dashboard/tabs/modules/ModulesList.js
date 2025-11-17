/**
 * External dependencies
 */
import { map, omit } from 'lodash'

/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data'
import { compose } from '@wordpress/compose'

/**
 * Internal dependencies
 */
import Module from './Module'

const ModulesList = ( { modules } ) => {
	// Move React Settings module to the end
	modules = {
		...omit( modules, 'react-settings' ),
		'react-settings': modules[ 'react-settings' ],
	}

	return map( modules, ( module, id ) => {
		if ( module.only === 'internal' ) {
			return
		}

		return <Module key={ id } id={ id } module={ module } />
	} )
}

export default compose(
	withSelect( ( select ) => {
		return {
			modules: select( 'rank-math-settings' ).getData(),
		}
	} )
)( ModulesList )
