/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { applyFilters, doAction } from '@wordpress/hooks'
import { withSelect, withDispatch } from '@wordpress/data'
import { Button, SelectControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Search from './Search'

const Header = ( { heading, range, updateDaysRange, onChange, postID = 0, slug = false, adminURL, homeURL } ) => {
	const dayRange = applyFilters( 'rank_math_analytics_day_range', [
		{ label: '7 Days', value: '-7 days' },
		{ label: '15 Days', value: '-15 days' },
		{ label: '30 Days', value: '-30 days' },
		{ label: '90 Days', value: '-3 months' },
	] )

	return (
		<div className="rank-math-analyzer-header">
			<h3 className="tab-title">
				<div>{ heading }</div>
				{ postID > 0 && (
					<Button
						isLink={ true }
						target="_blank"
						className="rank-math-edit-post"
						href={ adminURL + 'post.php?post=' + postID + '&action=edit' }
					>{ __( '[Edit]', 'rank-math' ) }</Button>
				) }
				{ slug && (
					<a
						className="rank-math-post-link"
						href={ homeURL + slug }
						target="_blank"
						rel="noreferrer"
					>
						{ slug }
					</a>
				) }
			</h3>

			<Search />

			<SelectControl
				label={ __( 'Timeframe', 'rank-math' ) }
				className="analytics-dropdown"
				value={ range }
				options={ dayRange }
				onChange={ ( value ) => {
					updateDaysRange( value )
					if ( onChange ) {
						onChange()
					}
				} }
			/>
		</div>
	)
}

export default compose(
	withSelect( ( select ) => {
		return { range: select( 'rank-math' ).getDaysRange() }
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateDaysRange( range ) {
				// Should Invalidate entire store first.
				dispatch( 'rank-math' ).invalidateResolutionForStore()
				doAction( 'rank_math_analytics_clear_store' )
				dispatch( 'rank-math' ).updateDaysRange( range )
			},
		}
	} )
)( Header )
