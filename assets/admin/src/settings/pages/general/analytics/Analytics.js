/**
 * WordPress Dependencies
 */
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'

/**
 * Internal Dependencies
 */
import Analytics from '../../../../wizard/steps/analytics'

export default compose(
	withSelect( ( select, props ) => {
		const store = select( 'rank-math-settings' )
		const data = store.getAnalytics()

		return {
			...props,
			data,
		}
	} ),

	withDispatch( ( dispatch, props ) => {
		const { data } = props

		return {
			updateData( key, value ) {
				const mapping = {
					searchConsole: data?.searchConsole || {},
					analyticsData: data?.analyticsData || {},
				}

				dispatch( 'rank-math-settings' ).updateAnalytics( {
					...data,
					[ key ]: value,
					...mapping,
				} )
			},
		}
	} )
)( Analytics )
