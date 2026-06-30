/**
 * AI Visibility — REST API service layer.
 *
 * Wraps `@wordpress/api-fetch` and unwraps the `{ success, data }` envelope.
 * All caching is server-side; these calls are cheap cache reads in steady state.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'
import { addQueryArgs } from '@wordpress/url'

export const NAMESPACE = '/rankmath/v1/ai-visibility'

/**
 * @param {string} path    REST path.
 * @param {Object} options apiFetch options.
 * @return {Promise<*>} Unwrapped `data` payload.
 */
const request = async ( path, options = {} ) => {
	const response = await apiFetch( { path, ...options } )
	return response && response.success === true ? response.data : response
}

/**
 * @param {string} path     Path under the namespace.
 * @param {Object} [params] Query params.
 * @return {string} Full path.
 */
const buildPath = ( path, params ) => {
	const full = `${ NAMESPACE }${ path }`
	return params && Object.keys( params ).length ? addQueryArgs( full, params ) : full
}

/**
 * GET /overview — summary + per-brand rollup rows.
 *
 * @param {Object}  [params]         Optional params.
 * @param {boolean} [params.refresh] Force upstream revalidation (SWR).
 * @param {string}  [params.search]  Server-side name/URL filter.
 * @return {Promise<{ summary: Object, brands: Array, is_stale: boolean }>} Overview data.
 */
export const getOverview = ( { refresh = false, search = '' } = {} ) =>
	request( buildPath( '/overview', {
		...( refresh ? { refresh: 1 } : {} ),
		...( search ? { search } : {} ),
	} ) )

/**
 * GET /brands/{id} — brand identity (cache-first).
 *
 * @param {string} id Brand UUID.
 * @return {Promise<{ brand: Object }>} Brand details.
 */
export const getBrand = ( id ) => request( buildPath( `/brands/${ id }` ) )

/**
 * POST /brands — create a brand.
 *
 * @param {Object} data Brand payload (name, url, description, locale).
 * @return {Promise<{ brand: Object }>} Created brand.
 */
export const createBrand = ( data ) =>
	request( buildPath( '/brands' ), { method: 'POST', data } )

/**
 * PUT /brands/{id} — update brand fields.
 *
 * @param {string} id   Brand UUID.
 * @param {Object} data Partial brand payload.
 * @return {Promise<{ brand: Object }>} Updated brand.
 */
export const updateBrand = ( id, data ) =>
	request( buildPath( `/brands/${ id }` ), { method: 'PUT', data } )

/**
 * PATCH /brands/{id} — set brand status ('active' or 'inactive').
 *
 * @param {string} id     Brand UUID.
 * @param {string} status New status.
 * @return {Promise<{ brand: Object }>} Updated brand.
 */
export const setBrandStatus = ( id, status ) =>
	request( buildPath( `/brands/${ id }` ), { method: 'PATCH', data: { status } } )

/**
 * Activate 15-day AI Visibility free trial.
 *
 * @return {Promise<{ activated: boolean }>} Activation result.
 */
export const activateTrial = () =>
	request( buildPath( '/trial/activate' ), { method: 'POST' } )

/**
 * Get an authenticated, single-use Content AI checkout iframe URL.
 *
 * Fetch a fresh URL per iframe session — the token in it expires in 15 min
 * and is consumed on first load, so never reload the iframe with the same URL.
 *
 * @return {Promise<{ url: string }>} Iframe src URL.
 */
export const getCheckoutUrl = () =>
	request( buildPath( '/checkout-url' ), { method: 'POST' } )

/**
 * GET /brands/{id}/insights — latest-analysis payload (cache-first).
 * Returns `{ pending: true }` when no completed analysis exists yet.
 *
 * @param {string} id Brand UUID.
 * @return {Promise<{ insights: Object }|{ pending: boolean }>} Latest insights or pending status.
 */
export const getInsights = ( id ) => request( buildPath( `/brands/${ id }/insights` ) )

/**
 * GET /brands/{id}/queries — query list (cache-first, 24h TTL).
 *
 * @param {string} id Brand UUID.
 * @return {Promise<{ queries: Array }>} List of queries with their current enabled/disabled status and performance metrics.
 */
export const getQueries = ( id ) => request( buildPath( `/brands/${ id }/queries` ) )

/**
 * PUT /brands/{id}/queries/{queryId} — enable/disable a query.
 *
 * @param {string} id      Brand UUID.
 * @param {string} queryId Query UUID.
 * @param {Object} data    `{ enabled }`.
 * @return {Promise<{ query: Object }>} Updated query.
 */
export const updateQuery = ( id, queryId, data ) =>
	request( buildPath( `/brands/${ id }/queries/${ queryId }` ), { method: 'PUT', data } )

/**
 * POST /brands/{id}/generate-queries — regenerate baseline queries.
 *
 * @param {string} id Brand UUID.
 * @return {Promise<{ queries: Array }>} New query list (same shape as GET /queries).
 */
export const generateQueries = ( id ) =>
	request( buildPath( `/brands/${ id }/generate-queries` ), { method: 'POST' } )
