/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update twitter player url.
 *
 * @param {string} playerUrl The new player Url.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterPlayerUrl( playerUrl ) {
	return updateAppData(
		'twitterPlayerUrl',
		playerUrl,
		'rank_math_twitter_player_url'
	)
}

/**
 * Update twitter player size.
 *
 * @param {string} playerSize The new player size.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterPlayerSize( playerSize ) {
	return updateAppData(
		'twitterPlayerSize',
		playerSize,
		'rank_math_twitter_player_size'
	)
}

/**
 * Update twitter stream url.
 *
 * @param {string} streamUrl The new stream url.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterPlayerStreamUrl( streamUrl ) {
	return updateAppData(
		'twitterPlayerStream',
		streamUrl,
		'rank_math_twitter_player_stream'
	)
}

/**
 * Update twitter stream ctype.
 *
 * @param {string} ctype The new stream ctype.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterPlayerStreamCtype( ctype ) {
	return updateAppData(
		'twitterPlayerStreamCtype',
		ctype,
		'rank_math_twitter_player_stream_ctype'
	)
}
