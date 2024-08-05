/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Button from '../buttons/Button'
import './scss/UploadFile.scss'

/**
 * Upload File component.
 *
 * @param {Object} props      Component props.
 * @param {string} props.desc File description.
 * @param {string} props.name WP Media title.
 */
export default ( { desc, name } ) => {
	const [ selectedImg, setSelectedImg ] = useState( {} )

	// Create a media frame for file selection.
	const mediaFrame = wp.media( {
		title: name,
		multiple: false,
		library: { type: 'image' },
		button: { text: 'Use this file' },
	} )

	// Event listener for file selection.
	mediaFrame.on( 'select', () => {
		const attachment = mediaFrame.state().get( 'selection' ).first().toJSON()
		setSelectedImg( attachment )
	} )

	return (
		<>
			<Button variant="secondary" onClick={ () => mediaFrame.open() }>
				{ __( 'Add or Upload File', 'rank-math' ) }
			</Button>

			<p
				className="field-description"
				dangerouslySetInnerHTML={ { __html: desc } }
			/>

			{ ! isEmpty( selectedImg ) && (
				<div className="media-status">
					<div className="img-status media-item">
						<img
							width={ 350 }
							height={ 196 }
							src={ selectedImg.url }
							alt={ selectedImg.alt }
							title={ selectedImg.filename }
						/>

						<Button
							className="remove-file-button"
							onClick={ () => setSelectedImg( {} ) }
						/>
					</div>
				</div>
			) }
		</>
	)
}
