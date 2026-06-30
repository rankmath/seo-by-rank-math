/**
 * DateInput — calendar icon + native date picker.
 *
 * @since 1.0.273
 */

/**
 * Internal dependencies
 */
import './DateInput.scss'

/**
 * DateInput component.
 *
 * @param {Object}   props
 * @param {string}   props.value       Current date value (YYYY-MM-DD).
 * @param {Function} props.onChange    Change handler — receives the native event.
 * @param {string}   props.ariaLabel   Accessible label for the input.
 * @param {string}   [props.id]        Optional id attribute for the input.
 * @param {string}   [props.className] CSS class for the input element.
 * @return {JSX.Element} Calendar icon + date input.
 */
const DateInput = ( { value, onChange, ariaLabel, id, className } ) => (
	<div className="rank-math-ai-visibility-date-input">
		<input
			type="date"
			id={ id }
			value={ value }
			onChange={ onChange }
			aria-label={ ariaLabel }
			className={ className }
		/>
	</div>
)

DateInput.displayName = 'DateInput'

export default DateInput
