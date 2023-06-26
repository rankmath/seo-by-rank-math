/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import classnames from 'classnames'

const PreviewDevices = ( { type, updatePreviewType } ) => {
	const desktopClassess = classnames(
		'button button-secondary button-small',
		{ active: 'desktop' === type }
	)
	const mobileClassess = classnames(
		'button button-secondary button-small',
		{ active: 'mobile' === type }
	)
	return (
		<div className="rank-math-button-devices alignright">
			{ applyFilters( 'rank_math_before_serp_devices', '' ) }

			<div
				onClick={ () => updatePreviewType( 'desktop' ) }
				className={ desktopClassess }
			>
				<i className="rm-icon rm-icon-desktop"></i>
			</div>
			<div
				onClick={ () => updatePreviewType( 'mobile' ) }
				className={ mobileClassess }
			>
				<i className="rm-icon rm-icon-mobile"></i>
			</div>
		</div>
	)
}

export default compose(
	withSelect( ( select ) => {
		return {
			type: select( 'rank-math' ).getSnippetPreviewType(),
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		return {
			updatePreviewType( check ) {
				dispatch( 'rank-math' ).updateSnippetPreviewType(
					check === props.type ? '' : check
				)
			},
		}
	} )
)( PreviewDevices )
