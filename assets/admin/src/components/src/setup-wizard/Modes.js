/**
 * External dependencies
 */
import { map } from 'lodash'
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { createElement } from '@wordpress/element'

/**
 * Internal dependencies
 */
import './scss/Modes.scss'
import '../controls/scss/RadioControl.scss'

/**
 * Modes component.
 *
 * @param {Object}   props           Component props.
 * @param {string}   props.value     The current value selected.
 * @param {Function} props.onChange  Callback invoked when an option is changed.
 * @param {Array}    props.options   An array of mode options. An option must have at least a "title" and "value" property.
 * @param {string}   props.className CSS class for additional styling.
 */
export default ( { value, onChange, options = [], className = '', ...additionalProps } ) => {
	className = `rank-math-modes ${ className }`

	return (
		<ul { ...additionalProps } className={ className }>
			{ map(
				options,
				( {
					value: optionValue,
					title,
					children,
					description,
					disabled,
				} ) => {
					const checked = optionValue === value

					return (
						<li key={ optionValue }>
							<div className="metabox rank-math-radio-control components-radio-control">
								<input
									type="radio"
									name="mode-option"
									checked={ checked }
									id={ optionValue }
									value={ optionValue }
									onChange={ onChange }
									disabled={ disabled }
									className="components-radio-control__input"
								/>
							</div>

							<label
								htmlFor={ optionValue }
								className={ classNames( {
									'is-checked': checked,
									'is-disabled': disabled,
								} ) }
							>
								<div className="rank-math-mode-title">
									{ title }
								</div>

								{ children && (
									<div className="children">
										{ createElement( children ) }
									</div>
								) }

								<p>{ description }</p>
							</label>
						</li>
					)
				}
			) }
		</ul>
	)
}
