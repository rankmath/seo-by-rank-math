/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useEffect, useState } from '@wordpress/element'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import fields from './helpers/fields'
import validateForm from './helpers/validateForm'
import setupEditingState from './helpers/setupEditingState'
import separateRedirectionTypes from './helpers/separateRedirectionTypes'
import TabContent from '@rank-math-settings/components/TabContent'

const Form = ( { defaultRedirection, settings, updateData } ) => {
	const { isNew, data } = rankMath
	const [ isEditing, setIsEditing ] = useState( ! isEmpty( data ) )

	useEffect( separateRedirectionTypes, [] )

	useEffect( () => setupEditingState( setIsEditing ), [] )

	useEffect( () => {
		if ( ! rankMath.data ) {
			updateData( defaultRedirection )
		}
	}, [] )

	return (
		<div
			className={ `rank-math-redirections-form rank-math-editcreate-form rank-math-page rank-math-box ${
				isNew || isEditing ? 'is-open' : ''
			}` }
		>
			<h2>
				<strong>
					{ isEditing
						? __( 'Update Redirection', 'rank-math' )
						: __( 'Add Redirection', 'rank-math' ) }
				</strong>
			</h2>

			<TabContent
				type="redirections"
				fields={ fields() }
				settings={ settings }
				settingType="redirections"
				footer={ {
					discardButton: {
						onClick: undefined,
						isDestructive: true,
						children: __( 'Cancel', 'rank-math' ),
					},
					applyButton: {
						type: 'submit',
						validate: validateForm,
						children:
							isEditing
								? __( 'Update Redirection', 'rank-math' )
								: __( 'Add Redirection', 'rank-math' ),
						afterSave: () =>
							( window.location.href = `${ rankMath.adminurl }?page=rank-math-redirections` ),
					},
				} }
			/>
		</div>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		return {
			defaultRedirection: props.defaultRedirection,
			settings: select( 'rank-math-settings' ).getData(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateData( data ) {
				dispatch( 'rank-math-settings' ).updateData( { ...data } )
			},
		}
	} )
)( Form )
