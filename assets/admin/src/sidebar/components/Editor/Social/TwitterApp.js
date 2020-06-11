/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { TextControl } from '@wordpress/components'
import { withDispatch, withSelect } from '@wordpress/data'

const iPhoneFields = ( props ) => (
	<Fragment>
		<TextControl
			value={ props.iphoneName }
			label={ __( 'iPhone App Name', 'rank-math' ) }
			help={ __( 'The name of your app to show.', 'rank-math' ) }
			onChange={ props.updateAppIphoneName }
		/>

		<TextControl
			value={ props.iphoneID }
			label={ __( 'iPhone App ID', 'rank-math' ) }
			help={ __(
				'The numeric representation of your app ID in the App Store.',
				'rank-math'
			) }
			onChange={ props.updateAppIphoneID }
		/>

		<TextControl
			value={ props.iphoneUrl }
			label={ __( 'iPhone App URL', 'rank-math' ) }
			help={ __(
				"Your app's custom URL scheme (must include ://).",
				'rank-math'
			) }
			onChange={ props.updateAppIphoneUrl }
		/>
	</Fragment>
)

const iPadFields = ( props ) => (
	<Fragment>
		<TextControl
			value={ props.ipadName }
			label={ __( 'iPad App Name', 'rank-math' ) }
			help={ __( 'The name of your app to show.', 'rank-math' ) }
			onChange={ props.updateAppIpadName }
		/>

		<TextControl
			value={ props.ipadID }
			label={ __( 'iPad App ID', 'rank-math' ) }
			help={ __(
				'The numeric representation of your app ID in the App Store.',
				'rank-math'
			) }
			onChange={ props.updateAppIpadID }
		/>

		<TextControl
			value={ props.ipadUrl }
			label={ __( 'iPad App URL', 'rank-math' ) }
			help={ __(
				"Your app's custom URL scheme (must include ://).",
				'rank-math'
			) }
			onChange={ props.updateAppIpadUrl }
		/>
	</Fragment>
)

const GooglePlayFields = ( props ) => (
	<Fragment>
		<TextControl
			value={ props.googleplayName }
			label={ __( 'Google Play App Name', 'rank-math' ) }
			help={ __( 'The name of your app to show.', 'rank-math' ) }
			onChange={ props.updateAppGoogleplayName }
		/>

		<TextControl
			value={ props.googleplayID }
			label={ __( 'Google Play App ID', 'rank-math' ) }
			help={ __(
				'Your app ID in the Google Play (.i.e. com.android.app)',
				'rank-math'
			) }
			onChange={ props.updateAppGoogleplayID }
		/>

		<TextControl
			value={ props.googleplayUrl }
			label={ __( 'Google Play App URL', 'rank-math' ) }
			help={ __(
				"Your app's custom URL scheme (must include ://).",
				'rank-math'
			) }
			onChange={ props.updateAppGoogleplayUrl }
		/>
	</Fragment>
)

const TwitterApp = ( props ) => (
	<Fragment>
		<TextControl
			value={ props.description }
			label={ __( 'App Description', 'rank-math' ) }
			help={ __(
				'You can use this as a more concise description than what you may have on the app store. This field has a maximum of 200 characters. (optional)',
				'rank-math'
			) }
			onChange={ props.updateAppDescription }
		/>

		{ iPhoneFields( props ) }

		{ iPadFields( props ) }

		{ GooglePlayFields( props ) }

		<TextControl
			value={ props.country }
			label={ __( 'App Country', 'rank-math' ) }
			help={ __(
				'If your application is not available in the US App Store, you must set this value to the two-letter country code for the App Store that contains your application.',
				'rank-math'
			) }
			onChange={ props.updateAppCountry }
		/>
	</Fragment>
)

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )

		return {
			country: repo.getTwitterAppCountry(),
			description: repo.getTwitterAppDescription(),
			iphoneName: repo.getTwitterAppIphoneName(),
			iphoneID: repo.getTwitterAppIphoneID(),
			iphoneUrl: repo.getTwitterAppIphoneUrl(),
			ipadName: repo.getTwitterAppIpadName(),
			ipadID: repo.getTwitterAppIpadID(),
			ipadUrl: repo.getTwitterAppIpadUrl(),
			googleplayName: repo.getTwitterAppGoogleplayName(),
			googleplayID: repo.getTwitterAppGoogleplayID(),
			googleplayUrl: repo.getTwitterAppGoogleplayUrl(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateAppIphoneName( value ) {
				dispatch( 'rank-math' ).updateTwitterAppIphoneName( value )
			},

			updateAppIphoneID( value ) {
				dispatch( 'rank-math' ).updateTwitterAppIphoneID( value )
			},

			updateAppIphoneUrl( value ) {
				dispatch( 'rank-math' ).updateTwitterAppIphoneUrl( value )
			},

			updateAppIpadName( value ) {
				dispatch( 'rank-math' ).updateTwitterAppIpadName( value )
			},

			updateAppIpadID( value ) {
				dispatch( 'rank-math' ).updateTwitterAppIpadID( value )
			},

			updateAppIpadUrl( value ) {
				dispatch( 'rank-math' ).updateTwitterAppIpadUrl( value )
			},

			updateAppGoogleplayName( value ) {
				dispatch( 'rank-math' ).updateTwitterAppGoogleplayName( value )
			},

			updateAppGoogleplayID( value ) {
				dispatch( 'rank-math' ).updateTwitterAppGoogleplayID( value )
			},

			updateAppGoogleplayUrl( value ) {
				dispatch( 'rank-math' ).updateTwitterAppGoogleplayUrl( value )
			},

			updateAppDescription( value ) {
				dispatch( 'rank-math' ).updateTwitterAppDescription( value )
			},

			updateAppCountry( value ) {
				dispatch( 'rank-math' ).updateTwitterAppCountry( value )
			},
		}
	} )
)( TwitterApp )
