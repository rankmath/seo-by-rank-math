/**
 * External dependencies
 */
import { Helpers } from '@rankMath/analyzer'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { TextControl } from '@wordpress/components'
import { safeDecodeURIComponent } from '@wordpress/url'

/**
 * Internal dependencies
 */
import LengthIndicator from '@components/LengthIndicator'

const EditorPermalink = ( { permalink, serpPermalink, updatePermalink, updatePermalinkSanitize } ) => (
	<div className="field-group">
		<label htmlFor="rank-math-editor-permalink">
			{ __( 'Permalink', 'rank-math' ) }
		</label>

		<LengthIndicator
			source={ safeDecodeURIComponent( serpPermalink ) }
			min={ 5 }
			max={ 75 }
		/>

		<TextControl
			id="rank-math-editor-permalink"
			value={
				rankMath.is_front_page
					? '/'
					: safeDecodeURIComponent( permalink )
			}
			onChange={ updatePermalink }
			help={
				rankMath.is_front_page
					? __(
						'Editing Homepage permalink is not possible.',
						'rank-math'
					)
					: __(
						'This is the unique URL of this page, displayed below the post title in the search results.',
						'rank-math'
					)
			}
			disabled={ rankMath.is_front_page ? 'disabled' : '' }
			onBlur={ ( event ) => {
				updatePermalinkSanitize( event.target.value )
			} }
		/>
	</div>
)

export default compose(
	withSelect( ( select ) => {
		const editor = rankMathEditor.assessor.dataCollector
		const slug = select( 'rank-math' ).getSerpSlug()
		return {
			permalink: slug ? slug : editor.getSlug(),
			serpPermalink: editor.getPermalink(),
		}
	} ),
	withDispatch( () => {
		return {
			updatePermalink( slug ) {
				rankMathEditor.updatePermalink( Helpers.sanitizeText( slug ) )
			},
			updatePermalinkSanitize( slug ) {
				rankMathEditor.updatePermalinkSanitize( Helpers.sanitizeText( slug ) )
			},
		}
	} )
)( EditorPermalink )
