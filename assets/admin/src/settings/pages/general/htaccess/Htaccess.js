/**
 * External dependencies
 */
import { isEmpty, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { Notice, CheckboxControl, TextareaControl } from '@rank-math/components'

const showNotice = ( text ) => {
	return (
		<Notice status="error">
			{ text }
		</Notice>
	)
}

/**
 * Htaccess edit disclaimer.
 */
const Htaccess = ( { settings, updateSettings } ) => {
	const { isEditAllowed, htaccessData } = rankMath
	if ( isEmpty( htaccessData ) ) {
		return showNotice( __( '.htaccess file not found.', 'rank-math' ) )
	}

	const isWritable = htaccessData.writable && isEditAllowed

	return (
		<>
			{
				! isWritable && showNotice( __( '.htaccess file is not writable.', 'rank-math' ) )
			}
			{
				isWritable && showNotice(
					<>
						<RawHTML>
							{ __(
								'Be careful when editing the htaccess file, it is easy to make mistakes and break your site. If that happens, you can restore the file to its state <strong>before the last edit</strong> by replacing the htaccess file with the backup copy created by Rank Math in the same directory (<em>.htaccess_back_xxxxxx</em>) using an FTP client.',
								'rank-math'
							) }
						</RawHTML>
						<br />
						<CheckboxControl
							label={ (
								<strong>
									{ __(
										'I understand the risks and I want to edit the file',
										'rank-math'
									) }
								</strong>
							) }
							checked={ settings.htaccess_allow_editing }
							onChange={ ( isAllowed ) => updateSettings( 'htaccess_allow_editing', isAllowed ) }
						/>
					</>
				)
			}
			<br /><br />
			<TextareaControl
				id="htaccess_content"
				name="htaccess_content"
				className="rank-math-code-box"
				variant="code-box"
				value={ settings.htaccess_content }
				readOnly={ ! isWritable || ! settings.htaccess_allow_editing }
				data-gramm={ false }
				rows={ 10 }
				onChange={ ( text ) => updateSettings( 'htaccess_content', text ) }
				__nextHasNoMarginBottom={ true }
			/>

		</>
	)
}

export default compose(
	withSelect( ( select ) => {
		const { htaccessData } = rankMath
		const settings = select( 'rank-math-settings' ).getData()
		if ( isUndefined( settings.htaccess_allow_editing ) && ! isEmpty( htaccessData ) ) {
			settings.htaccess_allow_editing = false
			settings.htaccess_content = htaccessData.content
		}

		return {
			settings,
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		const { settings } = props
		return {
			updateSettings( key, value ) {
				settings[ key ] = value
				dispatch( 'rank-math-settings' ).updateData( { ...settings } )
			},
		}
	} )
)( Htaccess )
