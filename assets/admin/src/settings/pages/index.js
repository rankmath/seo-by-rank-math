/**
 * External Dependencies
 */
import { map, pick, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { applyFilters } from '@wordpress/hooks'

const configMap = {
	general: () => import( /* webpackChunkName: "generalSettings" */ './general' ),
	titles: () => import( /* webpackChunkName: "titleSettings" */ './titles' ),
	sitemap: () => import( /* webpackChunkName: "sitemapSettings" */ './sitemap' ),
	'instant-indexing': () => import( /* webpackChunkName: "instantIndexingSettings" */ './instantIndexing' ),
}

export default async ( pageSlug, allowedTabs = [] ) => {
	const pageLoader = configMap[ pageSlug ]
	if ( ! pageLoader ) {
		return null
	}

	const module = await pageLoader()
	let pageConfig = applyFilters( `rank_math_${ pageSlug }_options__tabs`, module.default )
	pageConfig = isEmpty( allowedTabs ) ? pageConfig : pick( pageConfig, allowedTabs )

	// Filter tabs based on allowedTabs from PHP
	return map(
		pageConfig,
		( tabConfig, key ) => {
			tabConfig.fields = applyFilters( `rank_math_${ pageSlug }_options__fields`, tabConfig.fields, key )
			tabConfig.fields = applyFilters( `rank_math_${ pageSlug }_options__${ key }`, tabConfig.fields )
			return ( {
				...tabConfig,
			} )
		}
	)
}
