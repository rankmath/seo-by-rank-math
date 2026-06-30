/**
 * LoadingButton — Button with built-in loading state.
 *
 * Renders a WP Spinner to the left of the label while loading.
 * Never changes the button colour — only adds a spinner and swaps the text.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Button from './Button'

/**
 * @param {Object}  props
 * @param {boolean} [props.isLoading=false] Swaps label and disables the button. If `iconLeft` is
 *                                          provided, also replaces it with a Spinner while loading.
 * @param {string}  [props.loadingLabel]    Label shown while loading. Falls back to `children + '…'`.
 * @param {*}       props.children          Default label.
 * @param {boolean} [props.disabled]        Additional disabled condition (independent of isLoading).
 * @param {*}       [props.iconLeft]        Icon shown when not loading. When provided, it is replaced
 *                                          by a Spinner while loading. Omit for text-only buttons.
 * @param {*}       [props.iconRight]       Icon shown to the right; unaffected by loading state.
 * @param {string}  [props.variant]         WP Button variant passed through.
 * @return {JSX.Element} Button with loading state support.
 */
const LoadingButton = ( {
	isLoading = false,
	loadingLabel,
	children,
	disabled,
	iconLeft,
	...rest
} ) => {
	const label = isLoading ? ( loadingLabel ?? `${ children }…` ) : children
	const leftIcon = iconLeft && isLoading ? <Spinner /> : iconLeft

	return (
		<Button
			iconLeft={ leftIcon }
			disabled={ disabled || isLoading }
			{ ...rest }
		>
			{ label }
		</Button>
	)
}

export default LoadingButton
