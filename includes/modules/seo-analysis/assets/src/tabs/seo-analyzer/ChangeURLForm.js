/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import { Button, TextControl } from '@rank-math/components'
import { useAnalyzerContext } from './context'

export default () => {
	const [ showForm, setShowForm ] = useState( false )
	const { url, updateUrl, startAnalysis } = useAnalyzerContext()

	/**
	 * Show change-url form
	 */
	const handleChangeUrlClick = () => {
		setShowForm( true )
	}

	/**
	 * Initiate analysis when the change-url form is submitted
	 *
	 * @param {Event} event
	 */
	const handleSubmit = ( event ) => {
		event.preventDefault()

		setShowForm( false )
		startAnalysis()
	}

	return (
		<p className="page-analysis-selected">
			{ __( 'Selected page: ', 'rank-math' ) }

			{ showForm ? (
				<form className="changeurl-form" onSubmit={ handleSubmit }>
					<TextControl
						className="rank-math-analyze-url"
						variant="default"
						value={ url }
						onChange={ updateUrl }
					/>
					&nbsp;
					<Button
						type="submit"
						variant="secondary"
						className="rank-math-changeurl-ok"
						disabled={ ! url }
					>
						{ __( 'OK', 'rank-math' ) }
					</Button>
				</form>
			) : (
				<>
					<a
						href={ url }
						target="_blank"
						rel="noreferrer"
						className="rank-math-current-url"
					>
						{ url }
					</a>
					&nbsp;
					<Button
						variant="secondary"
						className="rank-math-changeurl"
						onClick={ handleChangeUrlClick }
					>
						{ __( 'Change URL', 'rank-math' ) }
					</Button>
				</>
			) }
		</p>
	)
}
