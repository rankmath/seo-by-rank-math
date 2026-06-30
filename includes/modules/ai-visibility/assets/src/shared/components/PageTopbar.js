/**
 * PageTopbar — shared top-bar layout for full-page detail views.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import Button from './Button'
import './PageTopbar.scss'

/**
 * PageTopbar component.
 *
 * @param {Object}      props
 * @param {Function}    props.onBack         Handler for the Back button.
 * @param {string}      [props.backLabel]    Label for the Back button. Defaults to "← Back".
 * @param {string}      [props.title]        Page title shown to the right of Back.
 * @param {JSX.Element} [props.actions]      Right-side action buttons.
 * @param {string}      [props.className=''] Extra class on the root element.
 * @return {JSX.Element} Topbar with back + title on the left, actions on the right.
 */
const PageTopbar = ( {
	onBack,
	backLabel,
	title,
	actions,
	className = '',
} ) => {
	const ns = 'rank-math-ai-visibility-page-topbar'

	return (
		<div className={ [ ns, className ].filter( Boolean ).join( ' ' ) }>
			<div className={ `${ ns }__left` }>
				<Button onClick={ onBack }>
					{ backLabel || __( '← Back', 'seo-by-rank-math' ) }
				</Button>
				{ title && (
					<span className={ `${ ns }__title` }>{ title }</span>
				) }
			</div>
			{ actions && (
				<div className={ `${ ns }__actions` }>{ actions }</div>
			) }
		</div>
	)
}

PageTopbar.displayName = 'PageTopbar'

export default PageTopbar
