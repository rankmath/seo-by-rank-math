/**
 * Internal dependencies
 */
import { swapVariables } from '@helpers/swapVariables'
import { updateAppData } from './metadata'

/**
 * Update keywords.
 *
 * @param {Array} keywords The new keywords.
 *
 * @return {Object} An action for redux.
 */
export function updateKeywords( keywords ) {
	swapVariables.setVariable( 'focuskw', keywords.split( ',' )[ 0 ] )
	rankMathEditor.refresh( 'keyword' )
	return updateAppData( 'keywords', keywords, 'rank_math_focus_keyword' )
}

/**
 * Update pillarContent.
 *
 * @param {boolean} pillarContent The new pillarContent.
 *
 * @return {Object} An action for redux.
 */
export function updatePillarContent( pillarContent ) {
	return updateAppData(
		'pillarContent',
		pillarContent,
		'rank_math_pillar_content',
		true === pillarContent ? 'on' : 'off'
	)
}

/**
 * Toggle FrontendScore.
 *
 * @param {boolean} value The new value.
 *
 * @return {Object} An action for redux.
 */
export function toggleFrontendScore( value ) {
	return updateAppData(
		'showScoreFrontend',
		value,
		'rank_math_dont_show_seo_score',
		true === value ? 'off' : 'on'
	)
}

/**
 * Update analysis score.
 *
 * @param {number} score The new score.
 *
 * @return {Object} An action for redux.
 */
export function updateAnalysisScore( score ) {
	return updateAppData( 'score', score, 'rank_math_seo_score' )
}

/**
 * Update canonical Url.
 *
 * @param {string} url The new url.
 *
 * @return {Object} An action for redux.
 */
export function updateCanonicalUrl( url ) {
	return updateAppData( 'canonicalUrl', url, 'rank_math_canonical_url' )
}

/**
 * Update advanced robots.
 *
 * @param {Object} meta The new robots meta.
 *
 * @return {Object} An action for redux.
 */
export function updateAdvancedRobots( meta ) {
	return updateAppData( 'advancedRobots', meta, 'rank_math_advanced_robots' )
}

/**
 * Update robots.
 *
 * @param {Object} robots The new robots.
 *
 * @return {Object} An action for redux.
 */
export function updateRobots( robots ) {
	return updateAppData(
		'robots',
		robots,
		'rank_math_robots',
		Object.keys( robots )
	)
}

/**
 * Update breadcrumb title.
 *
 * @param {string} title The new title.
 *
 * @return {Object} An action for redux.
 */
export function updateBreadcrumbTitle( title ) {
	return updateAppData(
		'breadcrumbTitle',
		title,
		'rank_math_breadcrumb_title'
	)
}

/**
 * Reset dirty meta to null
 *
 * @return {Object} An action for redux.
 */
export function resetDirtyMetadata() {
	return updateAppData( 'dirtyMetadata', {} )
}
