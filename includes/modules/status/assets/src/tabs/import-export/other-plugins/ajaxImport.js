/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import ajax from '@helpers/ajax'
import updateSeoScores from './updateSeoScores'
import addLog from './addLog'

const ajaxImport = ( slug, actions, logger, setLogger, paged, callback ) => {
	if ( 0 === actions.length ) {
		const message = __( 'Import finished.', 'rank-math' )

		addLog( message, logger, setLogger )
		callback()
		return
	}

	const action = actions.shift()
	let message =
			'deactivate' === action
				? 'Deactivating plugin'
				: 'Importing ' + action

	paged = paged || 1

	if ( 'recalculate' === action ) {
		message = __( 'Starting SEO score recalculation', 'rank-math' )
	}

	addLog( message, logger, setLogger )
	ajax( 'import_plugin', {
		perform: action,
		pluginSlug: slug,
		paged,
	} )
		.done( ( result ) => {
			paged = 1
			if (
				result &&
				result.page &&
				result.page < result.total_pages
			) {
				paged = result.page + 1
				actions.unshift( action )
			}

			if ( action === 'recalculate' && result.total_items > 0 ) {
				const { start, end, total_items: totalItems } = result
				updateSeoScores( result.data, logger, setLogger, () => {
					ajaxImport(
						slug,
						actions,
						logger,
						setLogger,
						paged,
						callback
					)
				}, start, end, totalItems )
			} else {
				if ( action === 'recalculate' && result.total_items === 0 ) {
					result.message = __( 'No posts found with SEO score.', 'rank-math' )
				}

				addLog( result.success ? result.message : result.error, logger, setLogger )
				ajaxImport(
					slug,
					actions,
					logger,
					setLogger,
					paged,
					callback
				)
			}
		} )
		.fail( ( result ) => {
			addLog( result.statusText, logger, setLogger )
			ajaxImport( slug, actions, logger, setLogger, null, callback )
		} )
}

export default ajaxImport
