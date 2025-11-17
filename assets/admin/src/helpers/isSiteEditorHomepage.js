/**
 * External dependencies
 */
import { endsWith } from 'lodash'

/**
 * Internal dependencies
 */
import isSiteEditorAvailable from '@helpers/isSiteEditorAvailable'

export default () => {
	return isSiteEditorAvailable() && endsWith( wp.data.select( 'core/edit-site' ).getEditedPostId(), '//home' )
}
