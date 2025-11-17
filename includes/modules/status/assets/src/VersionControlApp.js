/**
 * External Dependencies
 */
import { isEmpty, isUndefined } from 'lodash'

/**
 * WordPress Dependencies
 */
import { useEffect } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'

/**
 * Internal Dependencies
 */
import LoadingSkeleton from './LoadingSkeleton'
import VersionControl from './tabs/version-control'

const VersionControlApp = ( { data, getViewData, updateViewData } ) => {
	useEffect( () => {
		if ( ! isEmpty( data ) ) {
			return
		}

		getViewData()
	}, [] )

	return (
		isUndefined( data )
			? <LoadingSkeleton title="Version Control" />
			: <div className={ `rank-math-ui container version-control` }>
				<VersionControl data={ data } updateViewData={ updateViewData } />
			</div>
	)
}

export default compose(
	withSelect( ( select ) => (
		{
			data: select( 'rank-math-status' ).getViewData( 'version_control' ),
		}
	) ),
	withDispatch( ( dispatch ) => {
		const activeTab = 'version_control'
		return {
			getViewData() {
				apiFetch( {
					method: 'POST',
					path: '/rankmath/v1/status/getViewData',
					data: {
						activeTab,
					},
				} )
					.catch( ( error ) => {
						alert( error.message )
					} )
					.then( ( response ) => {
						dispatch( 'rank-math-status' ).updateViewData( activeTab, response )
					} )
			},
			updateViewData( value ) {
				dispatch( 'rank-math-status' ).updateViewData( activeTab, value )
			},
		}
	} )
)( VersionControlApp )
