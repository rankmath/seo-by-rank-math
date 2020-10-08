/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

export default ( params ) => {
	const noFilter = isEmpty( params.toString() )
	return {
		good: noFilter || params.getAll( 'filter' ).includes( 'good' ),
		ok: noFilter || params.getAll( 'filter' ).includes( 'ok' ),
		bad: noFilter || params.getAll( 'filter' ).includes( 'bad' ),
		noData: noFilter || params.getAll( 'filter' ).includes( 'noData' ),
	}
}
