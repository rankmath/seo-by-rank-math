/**
 * URL / query-param state helpers for the AI Visibility admin app.
 *
 * Tab state lives in `?tab=<slug>` (not hash). Transitions use pushState;
 * a popstate listener in App.js keeps React in sync with browser navigation.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { addQueryArgs, getQueryArg } from '@wordpress/url'

export const DEFAULT_TAB = 'dashboard'
export const DEFAULT_BRAND_TAB = 'overview'

/**
 * @param {string} fallback Fallback when no `tab` param is set.
 * @return {string} Active tab slug.
 */
export const getActiveTab = ( fallback = DEFAULT_TAB ) => {
	return getQueryArg( window.location.href, 'tab' ) || fallback
}

/**
 * Push a tab slug into the URL. No-op if already on that tab.
 *
 * @param {string} tabName Tab slug to activate.
 */
export const setTabInUrl = ( tabName ) => {
	if ( getActiveTab() === tabName ) {
		return
	}

	const url = addQueryArgs( window.location.href, { tab: tabName } )
	window.history.pushState( {}, '', url )
}

/**
 * @return {string|null} Brand UUID from the URL, or null.
 */
export const getActiveBrandId = () => {
	const raw = getQueryArg( window.location.href, 'brand' )
	return raw ? String( raw ) : null
}

/**
 * @param {number} brandId Brand ID to navigate to.
 */
export const navigateToBrand = ( brandId ) => {
	const url = addQueryArgs( window.location.href, {
		brand: brandId,
	} )
	window.history.pushState( {}, '', url )
}

/**
 * Navigate back to the Dashboard by removing brand detail params from the URL.
 */
export const navigateBackToDashboard = () => {
	const url = addQueryArgs( window.location.href, {
		brand: undefined,
		brand_tab: undefined,
	} )
	window.history.pushState( {}, '', url )
}

/**
 * @param {string} fallback Fallback when `brand_tab` param is absent.
 * @return {string} Active brand sub-tab slug.
 */
export const getActiveBrandTab = ( fallback = DEFAULT_BRAND_TAB ) =>
	getQueryArg( window.location.href, 'brand_tab' ) || fallback

/**
 * @param {string} tabName Brand detail sub-tab slug.
 */
export const setActiveBrandTabInUrl = ( tabName ) => {
	if ( getActiveBrandTab() === tabName ) {
		return
	}

	const url = addQueryArgs( window.location.href, { brand_tab: tabName } )
	window.history.pushState( {}, '', url )
}

/**
 * @return {string} Brand ID from `report_brand` param, or ''.
 */
export const getReportBrandParam = () => {
	const raw = getQueryArg( window.location.href, 'report_brand' )
	return raw ? String( raw ) : ''
}

/**
 * Navigate to Reports tab, clearing brand drill-down params. Dispatches
 * popstate so App.js picks up the tab change.
 *
 * @param {number|string} [brandId] Pre-populate the report brand filter.
 */
export const navigateToReportsTab = ( brandId ) => {
	const params = {
		tab: 'reports',
		brand: undefined,
		brand_tab: undefined,
		report_brand: brandId ? String( brandId ) : undefined,
	}
	const url = addQueryArgs( window.location.href, params )
	window.history.pushState( {}, '', url )
	window.dispatchEvent( new window.PopStateEvent( 'popstate' ) )
}

/**
 * @param {number} [fallback=1] Fallback when `aiv_page` is absent/invalid.
 * @return {number} Page number from the URL.
 */
export const getPageFromUrl = ( fallback = 1 ) => {
	const raw = parseInt( getQueryArg( window.location.href, 'aiv_page' ), 10 )
	return raw > 0 ? raw : fallback
}

/**
 * @param {number} [fallback=10] Fallback when `aiv_per` is absent/invalid.
 * @return {number} Page size from the URL.
 */
export const getPerPageFromUrl = ( fallback = 10 ) => {
	const raw = parseInt( getQueryArg( window.location.href, 'aiv_per' ), 10 )
	return raw > 0 ? raw : fallback
}

/**
 * @param {Object} pagination          `{ page, perPage }`.
 * @param {number} [defaultPerPage=10] Omitted from the URL when matched.
 */
export const writePaginationToUrl = ( pagination, defaultPerPage = 10 ) => {
	const url = addQueryArgs( window.location.href, {
		aiv_page: pagination.page > 1 ? pagination.page : undefined,
		aiv_per: pagination.perPage !== defaultPerPage ? pagination.perPage : undefined,
	} )
	window.history.replaceState( {}, '', url )
}
