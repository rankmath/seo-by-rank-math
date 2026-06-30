/**
 * Button — module-scoped primary button.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { Button as WpButton } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './Button.scss'

/**
 * AI-Visibility primary button.
 *
 * @param {Object}   props
 * @param {string}   [props.variant='primary'] WP Button variant ('primary'|'secondary'|'tertiary'|'link'|'icon').
 * @param {string}   [props.className]         Additional class names.
 * @param {string}   [props.iconVariant]       Icon-button colour sub-variant: 'danger' | 'active' | 'borderless'.
 *                                             Adds `--icon-{variant}` BEM modifier. Consumed here — never reaches DOM.
 * @param {*}        [props.iconLeft]          React node rendered before `children` (e.g. a WP icon).
 *                                             Consumed here — never reaches DOM.
 * @param {*}        [props.iconRight]         React node rendered after `children`.
 *                                             Consumed here — never reaches DOM.
 * @param {boolean}  [props.isDestructive]     Destructive styling (red).
 * @param {boolean}  [props.disabled]          Disabled state.
 * @param {boolean}  [props.isBusy]            Loading spinner state.
 * @param {Function} [props.onClick]           Click handler.
 * @param {*}        props.children            Button label / content.
 *
 * @return {JSX.Element} Button element with AI-Visibility styles and WP Button functionality.
 */
const Button = ( {
	variant = 'primary',
	className = '',
	iconVariant,
	iconLeft,
	iconRight,
	children,
	...rest
} ) => {
	const ns = 'rank-math-ai-visibility-btn'

	const cls = [
		ns,
		`${ ns }--${ variant }`,
		iconVariant && `${ ns }--icon-${ iconVariant }`,
		className,
	]
		.filter( Boolean )
		.join( ' ' )

	return (
		<WpButton
			variant={ variant }
			className={ cls }
			{ ...rest }
		>
			{ iconLeft && (
				<span className={ `${ ns }__icon-left` } aria-hidden="true">
					{ iconLeft }
				</span>
			) }
			{ children }
			{ iconRight && (
				<span className={ `${ ns }__icon-right` } aria-hidden="true">
					{ iconRight }
				</span>
			) }
		</WpButton>
	)
}

export default Button
