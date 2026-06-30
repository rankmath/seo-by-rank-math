/**
 * AI Visibility — top-level app shell.
 *
 * @since 1.0.273
 */

/**
 * External dependencies
 */
import { includes, toLower } from 'lodash'

/**
 * WordPress dependencies
 */
import { useState, useEffect, createElement } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Snackbar } from '@wordpress/components'

/**
 * Internal dependencies
 */
import { DashboardHeader, TabPanel } from '@rank-math/components'
import Dashboard from './Dashboard'
import Analyses from './Analyses'
import Reports from './Reports'
import { DEFAULT_TAB, getActiveTab, setTabInUrl } from './utils/urlState'
import { UpgradeToUnlockModal, ActivateTrialModal } from './shared/Modals'
import GatedPreview from './Dashboard/GatedPreview'
import Connect from './Connect'

/**
 * Build the tabs array consumed by `@rank-math/components` TabPanel.
 *
 * @return {Array<Object>} Tab config objects.
 */
const getTabs = () => [
	{
		name: DEFAULT_TAB,
		title: __( 'Dashboard', 'seo-by-rank-math' ),
		className: 'rank-math-ai-visibility-dashboard-tab rank-math-tab',
		view: Dashboard,
	},
	{
		name: 'analyses',
		title: __( 'Analyses & Transcripts', 'seo-by-rank-math' ),
		className: 'rank-math-ai-visibility-analyses-tab rank-math-tab',
		view: Analyses,
	},
	{
		name: 'reports',
		title: __( 'Reports & Export', 'seo-by-rank-math' ),
		className: 'rank-math-ai-visibility-reports-tab rank-math-tab',
		view: Reports,
	},
]

const App = ( { config = {} } ) => {
	const [ activeTab, setActiveTab ] = useState( getActiveTab( DEFAULT_TAB ) )
	const [ snackbar, setSnackbar ] = useState( null )

	const showSnackbar = ( message ) => setSnackbar( message )
	const dismissSnackbar = () => setSnackbar( null )

	// Sync with browser back/forward — pushState does NOT fire popstate.
	useEffect( () => {
		const handlePopState = () => {
			setActiveTab( getActiveTab( DEFAULT_TAB ) )
		}

		window.addEventListener( 'popstate', handlePopState )
		return () => window.removeEventListener( 'popstate', handlePopState )
	}, [] )

	const handleTabSelect = ( tabName ) => {
		setTabInUrl( tabName )
		setActiveTab( tabName )
	}

	const { isSiteConnected, isPro, plan, locales = [] } = rankMath.aiVisibility

	// Access gate — Free Content AI plan: PRO users get the trial flow,
	// free plugin users get the upgrade modal.
	const isFreePlan = includes( [ '', 'free' ], toLower( plan ) )
	const isPlanGated = isSiteConnected && isFreePlan

	return (
		<>
			<DashboardHeader page={ __( 'AI Visibility', 'seo-by-rank-math' ) } />
			<div className="wrap rank-math-wrap rank-math-ai-visibility">

				<TabPanel
					key={ activeTab }
					className="rank-math-tabs"
					activeClass="is-active"
					initialTabName={ activeTab }
					tabs={ getTabs() }
					onSelect={ handleTabSelect }
				>
					{ ( tab ) => (
						<div className={ 'rank-math-ai-visibility__tab rank-math-ai-visibility__tab--' + tab.name }>
							{ ! isSiteConnected && <Connect config={ config } /> }
							{ isSiteConnected && (
								isPlanGated ? <GatedPreview /> : createElement( tab.view, { locales, onBrandCreated: showSnackbar } )
							) }
						</div>
					) }
				</TabPanel>

				{ isPlanGated && ( isPro ? <ActivateTrialModal /> : <UpgradeToUnlockModal /> ) }
			</div>

			{ snackbar && (
				<Snackbar
					onRemove={ dismissSnackbar }
				>
					{ snackbar }
				</Snackbar>
			) }
		</>
	)
}

export default App
