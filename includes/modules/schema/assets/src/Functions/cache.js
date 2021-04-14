/**
 * External dependencies
 */
import { get, forEach, orderBy } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'
import { doAction, applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import schemaMaps from './schemaMaps'
import localStorage from '@helpers/localStorage'

/**
 * Local storage mape cache key.
 *
 * @type {string}
 */
const MAP_CACHE_KEY = 'rank_math_schema_templates_store'

/**
 * Map cache class.
 */
class MapCache {
	/**
	 * Cache holder.
	 *
	 * @type {Object}
	 */
	cache = null

	/**
	 * Templates holder.
	 *
	 * @type {Object}
	 */
	templates = null

	/**
	 * Schema version.
	 *
	 * @type {string}
	 */
	verion = '1.1.0'

	/**
	 * Constructor.
	 */
	constructor() {
		// if ( ! this.verifyCache() ) {
		// 	this.fetchStore()
		// }

		if ( 'product' !== rankMath.postType ) {
			delete schemaMaps.schemas.WooCommerceProduct
		}

		if ( 'download' !== rankMath.postType ) {
			delete schemaMaps.schemas.EDDProduct
		}

		this.cache = applyFilters( 'rank_math_schema_maps', schemaMaps )
		doAction( 'rank_math_schema_template_loaded' )
	}

	/**
	 * Verify if local cache is latest.
	 *
	 * @return {boolean} True if latest.
	 */
	verifyCache() {
		const cache = localStorage.get( MAP_CACHE_KEY )
		if ( false === cache || this.version !== cache.version ) {
			return false
		}

		this.cache = cache
		doAction( 'rank_math_schema_template_loaded' )

		return true
	}

	/**
	 * Fetch map store from api.
	 */
	fetchStore() {
		apiFetch( {
			method: 'GET',
			// url: '//schema.local/wp-json/rankmath/v1/getSchemas',
			url: '//' + window.location.host + '/wp-json/rankmath/v1/getSchemas',
		} ).then( ( response ) => {
			localStorage.set( MAP_CACHE_KEY, response, '30d' )
			this.cache = response
			doAction( 'rank_math_schema_template_loaded' )
		} )
	}

	/**
	 * Get schema map by id.
	 *
	 * @param  {string} mapId Map id.
	 * @return {Object|boolean} Map object if available or False.
	 */
	getMap( mapId ) {
		const map = get( this.cache.properties, mapId, false )

		return map ? map : get( this.cache.schemas, mapId, false )
	}

	/**
	 * Get pre-defined tempaltes.
	 *
	 * @return {Array} Array of templates.
	 */
	getTemplates() {
		if ( null === this.templates ) {
			this.templates = []
			forEach( this.cache.schemas, ( value, key ) => {
				const title = get( value, 'map.title', key )
				this.templates.push( {
					type: key,
					title: 'WooCommerceProduct' !== key ? title : __( 'WooCommerce Product', 'rank-math' ),
				} )
			} )

			this.templates = orderBy( this.templates, 'type' )
		}

		return this.templates
	}
}

/**
 * Cache singleton instance.
 *
 * @type {MapCache}
 */
export const mapCache = new MapCache()

/**
 * Get schema map by id.
 *
 * @param  {string} mapId Map id.
 * @return {Object|boolean} Map object if available or False.
 */
export function getMap( mapId ) {
	return mapCache.getMap( mapId )
}
