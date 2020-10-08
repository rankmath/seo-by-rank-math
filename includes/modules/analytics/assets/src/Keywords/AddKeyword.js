/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button, TextControl } from '@wordpress/components'
import { useState, Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { addKeyword } from '../functions'

const AddKeyword = () => {
	const [ isOpen, toggle ] = useState( false )
	const [ keyword, setKeyword ] = useState( false )

	return (
		<Fragment>
			<span>{ __( 'Keyword Manager', 'rank-math' ) }</span>
			{ ! isOpen && (
				<div className="add-keyword-button">
					<Button
						className="button button-secondary button-small add-keyword"
						onClick={ () => {
							toggle( ! isOpen )
						} }
					>
						{ __( 'Add', 'rank-math' ) }
					</Button>
				</div>
			) }
			{ isOpen && (
				<div className="add-keyword-button open">
					<TextControl
						placeholder={ __( 'Keyword', 'rank-math' ) }
						onChange={ setKeyword }
					/>
					<Button
						className="button button-primary button-small add-keyword"
						onClick={ () => {
							addKeyword( keyword )
							toggle( ! isOpen )
						} }
					>
						{ __( 'Save', 'rank-math' ) }
					</Button>
					<Button
						className="button button-secondary button-small button-link-delete cancel-keyword"
						onClick={ () => {
							toggle( ! isOpen )
						} }
					>
						{ __( 'Cancel', 'rank-math' ) }
					</Button>
				</div>
			) }
		</Fragment>
	)
}

export default AddKeyword
