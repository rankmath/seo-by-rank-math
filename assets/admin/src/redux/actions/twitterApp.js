/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update twitter app description.
 *
 * @param {string} value The new stream value.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppDescription( value ) {
	return updateAppData(
		'twitterAppDescription',
		value,
		'rank_math_twitter_app_description'
	)
}

/**
 * Update twitter app iphone id.
 *
 * @param {string} id The new app iphone id.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppIphoneID( id ) {
	return updateAppData(
		'twitterAppIphoneID',
		id,
		'rank_math_twitter_app_iphone_id'
	)
}

/**
 * Update twitter app iphone name.
 *
 * @param {string} name The new name.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppIphoneName( name ) {
	return updateAppData(
		'twitterAppIphoneName',
		name,
		'rank_math_twitter_app_iphone_name'
	)
}

/**
 * Update twitter app iphone url.
 *
 * @param {string} url The new app iphone url.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppIphoneUrl( url ) {
	return updateAppData(
		'twitterAppIphoneUrl',
		url,
		'rank_math_twitter_app_iphone_url'
	)
}

/**
 * Update twitter app ipad id.
 *
 * @param {string} id The new app ipad id.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppIpadID( id ) {
	return updateAppData(
		'twitterAppIpadID',
		id,
		'rank_math_twitter_app_ipad_id'
	)
}

/**
 * Update twitter app ipad name.
 *
 * @param {string} name The new app ipad name.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppIpadName( name ) {
	return updateAppData(
		'twitterAppIpadName',
		name,
		'rank_math_twitter_app_ipad_name'
	)
}

/**
 * Update twitter app ipad url.
 *
 * @param {string} url The new app ipad url.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppIpadUrl( url ) {
	return updateAppData(
		'twitterAppIpadUrl',
		url,
		'rank_math_twitter_app_ipad_url'
	)
}

/**
 * Update twitter app google id.
 *
 * @param {string} id The new app google id.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppGoogleplayID( id ) {
	return updateAppData(
		'twitterAppGoogleplayID',
		id,
		'rank_math_twitter_app_googleplay_id'
	)
}

/**
 * Update twitter app google name.
 *
 * @param {string} name The new app google name.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppGoogleplayName( name ) {
	return updateAppData(
		'twitterAppGoogleplayName',
		name,
		'rank_math_twitter_app_googleplay_name'
	)
}

/**
 * Update twitter app google url.
 *
 * @param {string} url The new app google url.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppGoogleplayUrl( url ) {
	return updateAppData(
		'twitterAppGoogleplayUrl',
		url,
		'rank_math_twitter_app_googleplay_url'
	)
}

/**
 * Update twitter app country.
 *
 * @param {string} country The new app country.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAppCountry( country ) {
	return updateAppData(
		'twitterAppCountry',
		country,
		'rank_math_twitter_app_country'
	)
}
