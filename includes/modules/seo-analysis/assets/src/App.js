/**
 * External Dependencies
 */
import { BrowserRouter, useSearchParams } from 'react-router-dom'
import { fromPairs } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal Dependencies
 */
import SeoAnalyzer from './tabs/seo-analyzer'
import CompetitorAnalyzer from './tabs/competitor-analyzer'
import { TabPanel, DashboardHeader } from '@rank-math/components'
import { AnalyzerContextProvider } from './tabs/seo-analyzer/context'

const getTabs = () => {
	return applyFilters( 'rank_math_analyzer_tabs', [
		{
			name: 'seo_analyzer',
			title: (
				<>
					<i className="rm-icon rm-icon-analyzer" />
					{ __( 'SEO Analyzer', 'rank-math' ) }
				</>
			),
			view: SeoAnalyzer,
		},
		{
			name: 'competitor_analyzer',
			title: (
				<>
					<i className="rm-icon rm-icon-users" />
					{ __( 'Competitor Analyzer', 'rank-math' ) }
				</>
			),
			view: CompetitorAnalyzer,
		},
	] )
}

const App = () => {
	const [ searchParams, setSearchParams ] = useSearchParams()

	const activeTab = searchParams.get( 'view' ) || getTabs()[ 0 ].name

	const onTabSelect = ( tabName ) => ( setSearchParams( ( params ) => fromPairs( [ ...params, [ 'view', tabName ] ] ) ) )

	return (
		<>
			<DashboardHeader page={ activeTab } />

			<div className="wrap rank-math-wrap rank-math-seo-analysis-wrap dashboard">
				<TabPanel
					tabs={ getTabs() }
					key={ activeTab }
					initialTabName={ activeTab }
					onSelect={ ( tabName ) => ( onTabSelect( tabName ) ) }
				>
					{ ( { name, view: View } ) => (
						<div className={ `rank-math-ui seo-analysis ${ name }` }>
							<View onTabSelect={ onTabSelect } />
						</div>
					) }
				</TabPanel>
			</div>
		</>
	)
}

export default () => (
	<BrowserRouter>
		<AnalyzerContextProvider>
			<App />
		</AnalyzerContextProvider>
	</BrowserRouter>
)
