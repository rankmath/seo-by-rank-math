/**
 * External Dependencies
 */
import { fromPairs, isEmpty, isUndefined } from 'lodash'

/**
 * WordPress Dependencies
 */
import { useEffect, Suspense } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'

/**
 * Internal Dependencies
 */
import { TabPanel, DashboardHeader } from '@rank-math/components'
import tabs from './tabs'
import LoadingSkeleton from './LoadingSkeleton'

const App = ( { data, activeTab, onTabChange, getViewData, updateViewData } ) => {
	useEffect( () => {
		if ( ! isEmpty( data ) ) {
			return
		}

		getViewData()
	}, [ activeTab ] )

	return (
		<>
			<DashboardHeader page={ activeTab } />

			<span className="wp-header-end"></span>
			<div className="wrap rank-math-wrap rank-math-tools-wrap dashboard">
				<Suspense>
					<TabPanel
						tabs={ tabs }
						key={ activeTab }
						initialTabName={ activeTab }
						onSelect={ onTabChange }
					>
						{ ( { name, title, view: View } ) => (

							isUndefined( data )
								? <LoadingSkeleton title={ title } name={ name } />
								: <div className={ `rank-math-ui container ${ name }` }>
									<View data={ data } updateViewData={ updateViewData } />
								</div>
						) }
					</TabPanel>
				</Suspense>
			</div>
		</>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		const activeTab = props.searchParams.get( 'view' )
		const store = select( 'rank-math-status' )

		return {
			...props,
			data: store.getViewData( activeTab ),
			activeTab,
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		const { activeTab, setSearchParams } = props
		return {
			onTabChange( tabName ) {
				if ( tabName !== activeTab ) {
					setSearchParams( ( params ) => fromPairs( [ ...params, [ 'view', tabName ] ] ) )
				}
			},
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
)( App )
