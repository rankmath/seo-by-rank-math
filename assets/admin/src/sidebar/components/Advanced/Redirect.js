/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import {
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components'

const Redirect = ( props ) => (
	<Fragment>
		<ToggleControl
			className={ props.hasRedirect ? 'is-open' : 'is-close' }
			label={ __( 'Redirect', 'rank-math' ) }
			checked={ props.hasRedirect }
			onChange={ () => props.toggle() }
		/>

		<SelectControl
			label={ __( 'Redirection Type', 'rank-math' ) }
			className={ props.hasRedirect ? '' : 'hidden' }
			value={ props.redirectionType }
			onChange={ ( value ) =>
				props.updateRedirection( 'redirectionType', value )
			}
			options={ [
				{
					value: '301',
					label: __( '301 Permanent Move', 'rank-math' ),
				},
				{
					value: '302',
					label: __( '302 Temporary Move', 'rank-math' ),
				},
				{
					value: '307',
					label: __( '307 Temporary Redirect', 'rank-math' ),
				},
				{
					value: '410',
					label: __( '410 Content Deleted', 'rank-math' ),
				},
				{
					value: '451',
					label: __(
						'451 Content Unavailable for Legal Reasons',
						'rank-math'
					),
				},
			] }
		/>

		{ false === [ '410', '451' ].includes( props.redirectionType ) && (
			<TextControl
				type="url"
				autoComplete="off"
				label={ __( 'Destination URL', 'rank-math' ) }
				value={ props.redirectionUrl }
				placeholder="https://rankmath.com/"
				className={ props.hasRedirect ? '' : 'hidden' }
				onChange={ ( value ) =>
					props.updateRedirection( 'redirectionUrl', value )
				}
			/>
		) }

		<TextControl
			type="hidden"
			value={ props.redirectionID }
			className="hidden"
		/>
	</Fragment>
)

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )

		return {
			redirectionID: repo.getRedirectionID(),
			redirectionUrl: repo.getRedirectionUrl(),
			redirectionType: repo.getRedirectionType(),
			hasRedirect: repo.hasRedirect(),
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		return {
			toggle() {
				dispatch( 'rank-math' ).updateHasRedirect( ! props.hasRedirect )
				dispatch( 'rank-math' ).updateRedirectionItem( {
					...props,
					hasRedirect: ! props.hasRedirect,
				} )
			},

			updateRedirection( prop, value ) {
				dispatch( 'rank-math' ).updateRedirection( prop, value )
				dispatch( 'rank-math' ).updateRedirectionItem( {
					...props,
					[ prop ]: value,
				} )
			},
		}
	} )
)( Redirect )
