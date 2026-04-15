/**
 * LinksPage component.
 *
 * Main Links admin page with Posts and Links tabs.
 * Uses DashboardHeader and TabPanel from @rank-math/components.
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect, createElement } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Posts from './tabs/Posts'
import Links from './tabs/Links'
import BulkUpdate from './tabs/BulkUpdate'
import KeywordMaps from './tabs/KeywordMaps'
import { DashboardHeader, TabPanel } from '@rank-math/components'

/**
 * Parse URL hash to get active tab name.
 *
 * @param {string} hash URL hash string.
 * @return {string} Active tab name ('posts' or 'links').
 */
const getTabFromHash = ( hash ) => {
	if ( ! hash || hash === '#' ) {
		return 'posts'
	}
	const tabName = hash.replace( '#', '' ).split( '?' )[ 0 ]
	return tabName
}

const getTabs = () => [
	{
		id: 'posts',
		name: 'posts',
		title: __( 'Posts', 'rank-math' ),
		view: Posts,
		className: 'rank-math-posts-tab rank-math-tab',
	},
	{
		id: 'links',
		name: 'links',
		title: __( 'Links', 'rank-math' ),
		view: Links,
		className: 'rank-math-links-tab rank-math-tab',
	},
	{
		id: 'bulk-update',
		name: 'bulk-update',
		title: __( 'Bulk Update', 'rank-math' ),
		view: BulkUpdate,
		className: 'rank-math-bulk-update-tab rank-math-tab',
	},
	{
		id: 'keyword-maps',
		name: 'keyword-maps',
		title: __( 'Keyword Maps', 'rank-math' ),
		view: KeywordMaps,
		className: 'rank-math-keyword-maps-tab rank-math-tab',
	},
]

/**
 * Main Links page component.
 */
const LinksPage = () => {
	const [ currentHash, setCurrentHash ] = useState( window.location.hash )

	useEffect( () => {
		const handleHashChange = () => {
			setCurrentHash( window.location.hash )
		}
		window.addEventListener( 'hashchange', handleHashChange )
		return () => window.removeEventListener( 'hashchange', handleHashChange )
	}, [] )

	const activeTab = getTabFromHash( currentHash )

	return (
		<>
			<DashboardHeader page={ __( 'Link Genius', 'rank-math' ) } />
			<div className="wrap rank-math-wrap rank-math-links-page">
				<TabPanel
					key={ activeTab }
					className="rank-math-tabs"
					activeClass="is-active"
					initialTabName={ activeTab }
					tabs={ getTabs() }
					onSelect={ ( tabName ) => {
						const currentTabName = getTabFromHash( window.location.hash )
						if ( currentTabName !== tabName ) {
							window.location.hash = tabName
						}
					} }
				>
					{ ( tab ) => (
						<div className={ 'rank-math-tab-content rank-math-tab-content-' + tab.name }>
							{ createElement( tab.view, { key: tab.name } ) }
						</div>
					) }
				</TabPanel>
			</div>
		</>
	)
}

export default LinksPage
