/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update facebook title.
 *
 * @param {string} title The new title.
 *
 * @return {Object} An action for redux.
 */
export function updateFacebookTitle( title ) {
	return updateAppData( 'facebookTitle', title, 'rank_math_facebook_title' )
}

/**
 * Update facebook description.
 *
 * @param {string} description The new description.
 *
 * @return {Object} An action for redux.
 */
export function updateFacebookDescription( description ) {
	return updateAppData(
		'facebookDescription',
		description,
		'rank_math_facebook_description'
	)
}

/**
 * Update facebook image.
 *
 * @param {string} image The new image.
 *
 * @return {Object} An action for redux.
 */
export function updateFacebookImage( image ) {
	return updateAppData( 'facebookImage', image, 'rank_math_facebook_image' )
}

/**
 * Update facebook image id.
 *
 * @param {number} imageID The new image id.
 *
 * @return {Object} An action for redux.
 */
export function updateFacebookImageID( imageID ) {
	return updateAppData(
		'facebookImageID',
		imageID,
		'rank_math_facebook_image_id'
	)
}

/**
 * Update facebook image overlay.
 *
 * @param {boolean} hasOverlay The has overlay.
 *
 * @return {Object} An action for redux.
 */
export function updateFacebookHasOverlay( hasOverlay ) {
	return updateAppData(
		'facebookHasOverlay',
		hasOverlay,
		'rank_math_facebook_enable_image_overlay',
		true === hasOverlay ? 'on' : 'off'
	)
}

/**
 * Update facebook image overlay.
 *
 * @param {string} image The new image id.
 *
 * @return {Object} An action for redux.
 */
export function updateFacebookImageOverlay( image ) {
	return updateAppData(
		'facebookImageOverlay',
		image,
		'rank_math_facebook_image_overlay'
	)
}
