/**
 * External Dependencies
 */
import { fromPairs, includes } from 'lodash'
import { useSearchParams } from 'react-router-dom'

/**
 * Internal Dependencies
 */
import { tabs } from './tabs'
import { TabPanel, DashboardHeader } from '@rank-math/components'

export default () => {
	const [ searchParams, setSearchParams ] = useSearchParams( { view: tabs[ 0 ].name } )
	const activeTab = searchParams.get( 'view' )

	const handleTabSelect = ( tabName ) => {
		if ( tabName !== activeTab && ! includes( [ 'setup-wizard', 'import-export' ], tabName ) ) {
			setSearchParams( ( params ) => fromPairs( [ ...params, [ 'view', tabName ] ] ) )
		}
	}

	return (
		<>
			<DashboardHeader page={ activeTab } />
			<div className="wrap rank-math-wrap dashboard">
				<TabPanel
					tabs={ tabs }
					key={ activeTab }
					initialTabName={ activeTab }
					onSelect={ handleTabSelect }
				>
					{ ( { view: View } ) => View ? <View /> : null }
				</TabPanel>
			</div>
		</>
	)
}
