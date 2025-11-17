/**
 * External dependencies
 */
import { startsWith, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data'
import { addAction } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import isSiteEditorHomepage from '@helpers/isSiteEditorHomepage'

/**
 * SiteEditor class to execute on FSE pages.
 */
class SiteEditor {
	/**
	 * Class constructor.
	 */
	constructor() {
		addAction( 'rank_math_id_changed', 'rank-math', this.resetStore )
		addAction( 'rank_math_data_changed', 'rank-math', this.activateButton )
	}

	/**
	 * Reset store when a Page is changed from FSE.
	 */
	resetStore() {
		const post = select( 'core/editor' ).getCurrentPost()
		const data = isEmpty( post.rankMath ) ? rankMath : post.rankMath
		if ( isSiteEditorHomepage() ) {
			data.assessor = rankMath.homepageData.assessor
		}

		dispatch( 'rank-math' ).resetStore( data )

		if ( isSiteEditorHomepage() ) {
			dispatch( 'rank-math' ).toggleLoaded( true )
		}
	}

	/**
	 * Activate the Save button when Rank Math meta value is changed.
	 *
	 * @param {string} key     Meta key.
	 * @param {string} value   Meta value.
	 * @param {string} metaKey Meta key used in the Database.
	 */
	activateButton( key, value, metaKey ) {
		if ( ! startsWith( metaKey, 'rank_math' ) ) {
			return
		}

		dispatch( 'core/editor' ).editPost( { [ metaKey ]: value } )
	}
}

export default SiteEditor
