/**
 * External Dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'
import { ToggleControl } from '@wordpress/components'

const LockModifiedDate = ( { lock, onChange } ) => (
	<div>
		<ToggleControl
			label={ __( 'Lock Modified Date', 'rank-math' ) }
			checked={ lock }
			onChange={ ( value ) => onChange( value ) }
		/>
	</div>
)

export default compose(
	withSelect( ( select ) => {
		return {
			lock: select( 'rank-math' ).isModifiedDateLocked(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			onChange( lock ) {
				dispatch( 'rank-math' ).lockModifiedDate( lock )
				const store = wp.data.select( 'core/editor' )
				if ( ! isUndefined( store ) && ! isUndefined( store.getEditedPostAttribute( 'meta' ) ) ) {
					const meta = store.getEditedPostAttribute( 'meta' )
					const newMeta = {
						...meta,
						rank_math_lock_modified_date: lock,
					}
					dispatch( 'core/editor' ).editPost( { meta: newMeta } )
				}
			},
		}
	} )
)( LockModifiedDate )
