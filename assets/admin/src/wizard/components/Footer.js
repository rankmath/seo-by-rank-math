/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Button } from '@rank-math/components'

/**
 * Form Footer
 *
 * @param {Object}   props             Component props.
 * @param {Function} props.saveData    Callback to save the Setup Wizard data.
 * @param {Function} props.skipStep    Callback executed to skip to the next step in the wizard.
 * @param {string}   props.currentStep The current step.
 */
export default ( { saveData, skipStep, currentStep } ) => {
	return (
		<footer className="form-footer wp-core-ui rank-math-ui">
			{ currentStep !== 'schema-markup' && (
				<Button
					variant="secondary"
					className="button-skip"
					onClick={ skipStep }
				>
					{ __( 'Skip Step', 'rank-math' ) }
				</Button>
			) }

			<Button
				variant="primary"
				onClick={ () => {
					saveData()
				} }
			>
				{ __( 'Save and Continue', 'rank-math' ) }
			</Button>
		</footer>
	)
}
