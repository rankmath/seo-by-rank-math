/**
 * WordPress dependencies
 */
import { doAction, applyFilters } from '@wordpress/hooks'
import { dispatch } from '@wordpress/data'
/**
 * Update the app data in redux.
 *
 * @param {string}        key       The key for data to update.
 * @param {string|Object} value     The value to update.
 * @param {string}        metaKey   The key for data to update.
 * @param {string|Object} metaValue The value to update.
 *
 * @return {Object} An action for redux.
 */
export function updateAppData( key, value, metaKey = false, metaValue = null ) {
	if ( metaKey && metaKey !== 'rank_math_seo_score' ){
		dispatch( 'core/editor' ).editPost({meta:{rankmath:'saving_post'}})
	}

	value = applyFilters( 'rank_math_sanitize_data', value, key, metaKey )
	if ( null !== metaValue ) {
		metaValue = applyFilters(
			'rank_math_sanitize_meta_value',
			metaValue,
			key,
			metaKey
		)
	}

	metaValue = null === metaValue ? value : metaValue

	doAction( 'rank_math_data_changed', key, value, metaKey )

	return {
		type: 'RANK_MATH_APP_DATA',
		key,
		value,
		metaKey,
		metaValue,
	}
}

/**
 * Update the app ui data in redux.
 *
 * @param {string}         key   The key for data to update.
 * @param {Object|string}  value The value to update.
 *
 * @return {Object} An action for redux.
 */
export function updateAppUi( key, value ) {
	doAction( 'rank_math_update_app_ui', key, value )
	return {
		type: 'RANK_MATH_APP_UI',
		key,
		value,
	}
}
