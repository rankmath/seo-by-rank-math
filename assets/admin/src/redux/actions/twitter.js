/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update twitter title.
 *
 * @param {boolean} useFacebook The new title.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterUseFacebook( useFacebook ) {
	return updateAppData(
		'twitterUseFacebook',
		useFacebook,
		'rank_math_twitter_use_facebook',
		true === useFacebook ? 'on' : 'off'
	)
}

/**
 * Update twitter card type.
 *
 * @param {number} cardType The card type.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterCardType( cardType ) {
	return updateAppData(
		'twitterCardType',
		cardType,
		'rank_math_twitter_card_type'
	)
}

/**
 * Update twitter title.
 *
 * @param {string} title The new title.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterTitle( title ) {
	return updateAppData( 'twitterTitle', title, 'rank_math_twitter_title' )
}

/**
 * Update twitter description.
 *
 * @param {string} description The new description.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterDescription( description ) {
	return updateAppData(
		'twitterDescription',
		description,
		'rank_math_twitter_description'
	)
}

/**
 * Update twitter author.
 *
 * @param {string} author The new author.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterAuthor( author ) {
	return updateAppData( 'twitterAuthor', author, 'rank_math_twitter_author' )
}

/**
 * Update twitter image id.
 *
 * @param {number} imageID The new image id.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterImageID( imageID ) {
	return updateAppData(
		'twitterImageID',
		imageID,
		'rank_math_twitter_image_id'
	)
}

/**
 * Update twitter image.
 *
 * @param {string} image The new image.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterImage( image ) {
	return updateAppData( 'twitterImage', image, 'rank_math_twitter_image' )
}

/**
 * Update twitter image overlay.
 *
 * @param {boolean} hasOverlay The has overlay.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterHasOverlay( hasOverlay ) {
	return updateAppData(
		'twitterHasOverlay',
		hasOverlay,
		'rank_math_twitter_enable_image_overlay',
		true === hasOverlay ? 'on' : 'off'
	)
}

/**
 * Update twitter image overlay.
 *
 * @param {string} image The new image id.
 *
 * @return {Object} An action for redux.
 */
export function updateTwitterImageOverlay( image ) {
	return updateAppData(
		'twitterImageOverlay',
		image,
		'rank_math_twitter_image_overlay'
	)
}
