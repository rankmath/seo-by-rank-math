/**
 * EmptyState — centred icon + heading + description + optional CTA.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { Icon } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Button from './Button'
import './EmptyState.scss'

/**
 * EmptyState component.
 *
 * @param {Object}   props
 * @param {*}        props.icon        WordPress icon glyph (from `@wordpress/icons`) passed to <Icon>.
 * @param {string}   props.heading     Primary heading text.
 * @param {string}   props.description Supporting body copy.
 * @param {string}   [props.ctaLabel]  CTA button label. Button omitted when absent.
 * @param {Function} [props.onCta]     CTA click handler. Button omitted when absent.
 *
 * @return {JSX.Element} Centred icon, heading, description, and optional CTA button.
 */
const EmptyState = ( { icon, heading, description, ctaLabel, onCta } ) => {
	const hasCta = ctaLabel && onCta

	const ns = 'rank-math-ai-visibility-empty-state'

	return (
		<div className={ ns }>
			{ icon && (
				<div className={ `${ ns }__icon` } aria-hidden="true">
					<Icon icon={ icon } />
				</div>
			) }

			<h2 className={ `${ ns }__heading` }>
				{ heading }
			</h2>

			<p className={ `${ ns }__description` }>
				{ description }
			</p>

			{ hasCta && (
				<Button
					className={ `${ ns }__cta` }
					onClick={ onCta }
				>
					{ ctaLabel }
				</Button>
			) }
		</div>
	)
}

export default EmptyState
