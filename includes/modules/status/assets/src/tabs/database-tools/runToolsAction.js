/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { isUndefined, keys } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import addNotice from '@helpers/addNotice'
import addLog from '../import-export/other-plugins/addLog'
import updateSeoScores from '../import-export/other-plugins/updateSeoScores'

const batchSize = rankMath.batchSize
let count = 0
const updateScore = ( response, logger, setLogger, args ) => {
	if ( count === 0 ) {
		addLog( __( 'Starting SEO score recalculation', 'rank-math' ), logger, setLogger )
	}

	const offset = ! isUndefined( args.offset ) ? args.offset : 0
	const start = count + 1
	const end = keys( response ).length + offset
	const totalPosts = ! isUndefined( args.update_all_scores ) && args.update_all_scores ? rankMath.totalPosts : rankMath.totalPostsWithoutScore
	updateSeoScores( response, logger, setLogger, () => {
		count = count + batchSize
		if ( count >= totalPosts ) {
			addLog( __( 'The SEO Scores have been recalculated successfully!', 'rank-math' ), logger, setLogger )
			return
		}

		args.offset = count
		runToolsAction( 'update_seo_score', logger, setLogger, args )
	}, start, end, totalPosts )
}

export const runToolsAction = ( id, logger, setLogger, args ) => {
	const noticeLocation = jQuery( '.wp-header-end' )
	jQuery.ajax( {
		url: rankMath.api.root + 'rankmath/v1/toolsAction',
		method: 'POST',
		beforeSend( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', rankMath.restNonce )
		},
		data: {
			action: id,
			args,
		},
	} ).fail( ( response ) => {
		if ( response ) {
			if ( response.responseJSON && response.responseJSON.message ) {
				addNotice( response.responseJSON.message, 'error', noticeLocation )
			} else {
				addNotice( response.statusText, 'error', noticeLocation )
			}
		}
	} )
		.done( ( response ) => {
			if ( response ) {
				if ( id === 'update_seo_score' ) {
					updateScore( response, logger, setLogger, args )
					return
				}

				if ( typeof response === 'string' ) {
					addNotice( response, 'success', noticeLocation, false )
					return
				}

				if ( typeof response === 'object' && response.status && response.message ) {
					addNotice( response.message, response.status, noticeLocation, false )
					return
				}
			}

			addNotice( __( 'Something went wrong. Please try again later.', 'rank-math' ), 'error', noticeLocation )
		} )
}
