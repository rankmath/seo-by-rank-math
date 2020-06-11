/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { Fragment } from '@wordpress/element'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, TextControl } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Address from '@components/Address'

const PersonSnippet = ( props ) => (
	<Fragment>
		<PanelBody initialOpen={ true }>
			<TextControl
				type="email"
				label={ __( 'Email', 'rank-math' ) }
				value={ props.personEmail }
				onChange={ props.updateEmail }
			/>
		</PanelBody>

		<Address
			label={ __( 'Address', 'rank-math' ) }
			initialOpen={ true }
			value={ '' !== props.personAddress ? props.personAddress : {} }
			onChange={ props.updateAddress }
		/>

		<PanelBody initialOpen={ true }>
			<TextControl
				label={ __( 'Gender', 'rank-math' ) }
				value={ props.personGender }
				onChange={ props.updateGender }
			/>

			<TextControl
				label={ __( 'Job title', 'rank-math' ) }
				help={ __(
					'The job title of the person (for example, Financial Manager).',
					'rank-math'
				) }
				value={ props.personJobTitle }
				onChange={ props.updateJobTitle }
			/>
		</PanelBody>
	</Fragment>
)

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math' ).getRichSnippets()

		return {
			personEmail: data.personEmail,
			personAddress: data.personAddress,
			personGender: data.personGender,
			personJobTitle: data.personJobTitle,
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateEmail( email ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'personEmail',
					'person_email',
					email
				)
			},

			updateAddress( address ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'personAddress',
					'person_address',
					address
				)
			},

			updateGender( gender ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'personGender',
					'person_gender',
					gender
				)
			},

			updateJobTitle( title ) {
				dispatch( 'rank-math' ).updateRichSnippet(
					'personJobTitle',
					'person_job_title',
					title
				)
			},
		}
	} )
)( PersonSnippet )
