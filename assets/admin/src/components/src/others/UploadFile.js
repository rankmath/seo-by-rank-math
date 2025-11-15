/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import Button from '../buttons/Button'
import './scss/UploadFile.scss'

/**
 * Upload File component.
 *
 * @param {Object}   props             Component props.
 * @param {string}   props.name        WP Media title.
 * @param {Object}   props.value       The selected file.
 * @param {Function} props.onChange    Callback executed on file selection.
 * @param {string}   props.description File description.
 */
export default ( { value, onChange, name, description, buttonText } ) => {
	const mediaFrame = wp.media( {
		title: name,
		multiple: false,
		library: { type: 'image' },
		button: { text: __( 'Use this file', 'rank-math' ) },
	} )

	mediaFrame.on( 'select', () => {
		const attachment = mediaFrame.state().get( 'selection' ).first().toJSON()
		onChange( attachment )
	} )

	return (
		<>
			<Button variant="secondary" onClick={ () => mediaFrame.open() }>
				{ buttonText ?? __( 'Add or Upload File', 'rank-math' ) }
			</Button>

			{ description && (
				<p
					className="field-description"
					dangerouslySetInnerHTML={ { __html: description } }
				/>
			) }

			{ ! isEmpty( value ) && (
				<div className="media-status">
					<div className="img-status media-item">
						<img
							width={ 350 }
							height={ 196 }
							src={ value }
							alt=""
						/>

						<Button
							className="remove-file-button"
							onClick={ () => onChange( {} ) }
						/>
					</div>
				</div>
			) }
		</>
	)
}
