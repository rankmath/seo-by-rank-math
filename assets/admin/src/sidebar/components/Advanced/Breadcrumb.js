/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { BaseControl, TextControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Tooltip from '@components/Tooltip'

const BreadcrumbTitle = ( { title, onTitleChange } ) => (
	<BaseControl className="rank-math-breadcrumb-title">
		<span className="components-base-control__label">
			{ __( 'Breadcrumb Title', 'rank-math' ) }
			<Tooltip>
				{ __( 'Breadcrumb Title to use for this post', 'rank-math' ) }
			</Tooltip>
		</span>

		<TextControl
			value={ title }
			onChange={ ( value ) => onTitleChange( value ) }
		/>
	</BaseControl>
)

export default compose(
	withSelect( ( select ) => {
		return {
			title: select( 'rank-math' ).getBreadcrumbTitle(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			onTitleChange( title ) {
				dispatch( 'rank-math' ).updateBreadcrumbTitle( title )
			},
		}
	} )
)( BreadcrumbTitle )
