/**
 * URL State Management Utilities
 *
 * Handles URL hash parameter synchronization for table state.
 * Format: #tabname?param=value
 */

export const URL_PARAMS = {
	POST_TYPE: 'post_type',
	IS_ORPHAN: 'is_orphan',
	SEO_SCORE_RANGE: 'seo_score_range',
	LINK_TYPE: 'link_type',
	SOURCE_ID: 'source_id',
	TARGET_POST_ID: 'target_post_id',
}

/**
 * Parse current hash to get tab name and search params.
 *
 * @return {Object} Object with tabName and searchParams.
 */
export function parseHashParams() {
	const hash = window.location.hash // eslint-disable-line no-undef

	if ( ! hash || hash === '#' ) {
		return { tabName: 'posts', searchParams: new URLSearchParams() } // eslint-disable-line no-undef
	}

	const hashWithoutPrefix = hash.replace( '#', '' )
	const [ tabName, queryString ] = hashWithoutPrefix.split( '?' )

	return {
		tabName: tabName || 'posts',
		searchParams: new URLSearchParams( queryString || '' ), // eslint-disable-line no-undef
	}
}

/**
 * Extract Posts tab filters from URL search params.
 *
 * @param {URLSearchParams} searchParams URL search parameters.
 * @return {Object} Filter object for Posts tab.
 */
export function getPostsFiltersFromURL( searchParams ) {
	const postTypeParam = searchParams.get( URL_PARAMS.POST_TYPE ) || ''
	const postTypes = postTypeParam ? postTypeParam.split( ',' ).filter( Boolean ) : []

	return {
		post_type: postTypes.length > 0 ? postTypes : [],
		is_orphan: searchParams.get( URL_PARAMS.IS_ORPHAN ) || '',
		seo_score_range: searchParams.get( URL_PARAMS.SEO_SCORE_RANGE ) || '',
	}
}

/**
 * Set Posts tab filters in URL hash.
 *
 * @param {string} tabName Current tab name.
 * @param {Object} filters Filter object to serialize.
 */
export function setPostsFiltersInHash( tabName, filters ) {
	const { searchParams } = parseHashParams()

	searchParams.delete( URL_PARAMS.POST_TYPE )
	searchParams.delete( URL_PARAMS.IS_ORPHAN )
	searchParams.delete( URL_PARAMS.SEO_SCORE_RANGE )

	if ( filters.post_type && filters.post_type.length > 0 ) {
		searchParams.set( URL_PARAMS.POST_TYPE, filters.post_type.join( ',' ) )
	}
	if ( filters.is_orphan ) {
		searchParams.set( URL_PARAMS.IS_ORPHAN, filters.is_orphan )
	}
	if ( filters.seo_score_range ) {
		searchParams.set( URL_PARAMS.SEO_SCORE_RANGE, filters.seo_score_range )
	}

	// Reset to page 1 when filters change.
	searchParams.set( 'page', '1' )

	const queryString = searchParams.toString()
	window.location.hash = queryString ? `${ tabName }?${ queryString }` : tabName // eslint-disable-line no-undef
}

/**
 * Clear all Posts tab filters from URL hash.
 *
 * @param {string} tabName Current tab name.
 */
export function clearPostsFiltersFromHash( tabName ) {
	const { searchParams } = parseHashParams()
	searchParams.delete( URL_PARAMS.POST_TYPE )
	searchParams.delete( URL_PARAMS.IS_ORPHAN )
	searchParams.delete( URL_PARAMS.SEO_SCORE_RANGE )

	// Reset to page 1 when filters are cleared.
	searchParams.set( 'page', '1' )

	const queryString = searchParams.toString()
	window.location.hash = queryString ? `${ tabName }?${ queryString }` : tabName // eslint-disable-line no-undef
}

/**
 * Set link_type filter in URL hash for Links tab.
 *
 * @param {string} isInternal Filter value: '1' (internal), '0' (external), or '' (all).
 */
export function setLinkTypeInHash( isInternal ) {
	const { searchParams } = parseHashParams()

	searchParams.delete( URL_PARAMS.LINK_TYPE )

	if ( isInternal === '1' ) {
		searchParams.set( URL_PARAMS.LINK_TYPE, 'internal' )
	} else if ( isInternal === '0' ) {
		searchParams.set( URL_PARAMS.LINK_TYPE, 'external' )
	}

	const queryString = searchParams.toString()
	window.location.hash = queryString ? `links?${ queryString }` : 'links' // eslint-disable-line no-undef
}

/**
 * Navigate to Links tab with pre-set source/target filters.
 *
 * @param {string} tabName   Current tab name ('posts').
 * @param {Object} linkFilters Object with source_id, target_post_id, link_type.
 */
export function navigateToLinksTab( linkFilters ) {
	const searchParams = new URLSearchParams() // eslint-disable-line no-undef

	if ( linkFilters.source_id ) {
		searchParams.set( URL_PARAMS.SOURCE_ID, linkFilters.source_id )
	}
	if ( linkFilters.target_post_id ) {
		searchParams.set( URL_PARAMS.TARGET_POST_ID, linkFilters.target_post_id )
	}
	if ( linkFilters.link_type ) {
		searchParams.set( URL_PARAMS.LINK_TYPE, linkFilters.link_type )
	}

	const queryString = searchParams.toString()
	window.location.hash = queryString ? `links?${ queryString }` : 'links' // eslint-disable-line no-undef
}
