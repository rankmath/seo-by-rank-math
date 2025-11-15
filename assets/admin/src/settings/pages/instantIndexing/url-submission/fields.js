/**
 * External Dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress Dependencies
 */
import { __, _x } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal Dependencies
 */
import { TextareaControl, Button } from '@rank-math/components'
import addNotice from '@helpers/addNotice'

const Console = () => {
	const [ urls, setUrls ] = useState( '' )
	const [ isSubmitting, setSubmitting ] = useState( false )
	const noticeLocation = jQuery( '.instant-indexing-notice' )

	const handleSubmit = () => {
		setSubmitting( true )
		// action heres
		apiFetch( {
			method: 'POST',
			path: '/rankmath/v1/in/submitUrls',
			data: {
				urls,
			},
		} )
			.catch( ( error ) => {
				const message = typeof error.responseJSON.message !== 'undefined' ? error.responseJSON.message : __( 'An error occurred while submitting the URL.', 'rank-math' )
				addNotice( message, 'error', noticeLocation, 5000 )
				setUrls( '' )
				setSubmitting( false )
			} )
			.then( ( response ) => {
				if ( response.success ) {
					addNotice( response.message, 'success', noticeLocation, 5000 )
					setUrls( '' )
				} else {
					addNotice( response.message, 'error', noticeLocation, 5000 )
				}
				setSubmitting( false )
			} )
	}

	return (
		<>
			<div className="bing-api-description description">
				<p>
					{ __(
						'Insert URLs to send to the IndexNow API (one per line, up to 10,000):',
						'rank-math'
					) }
				</p>
			</div>

			<TextareaControl
				value={ urls }
				onChange={ setUrls }
				id="indexnow_urls"
				className="instant-indexing-urls"
				placeholder={
					window.location.origin +
					'/' +
					_x( 'hello-world', 'URL slug placeholder', 'rank-math' )
				}
			/>
			{ isSubmitting && <span className="spinner is-active" id="indexnow_spinner"></span> }

			<Button
				variant="primary"
				id="indexnow_sumit"
				disabled={ isSubmitting }
				onClick={ handleSubmit }
			>
				{ __( 'Submit URLs', 'rank-math' ) }
			</Button>

			<div className="instant-indexing-notice"></div>
		</>
	)
}

export default [
	{
		id: 'console',
		type: 'component',
		Component: Console,
	},
]
