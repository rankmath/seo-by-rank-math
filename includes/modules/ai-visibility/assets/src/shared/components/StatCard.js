/**
 * StatCard — dashboard summary card.
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
import { HelpTooltip } from '@rank-math/components'
import './StatCard.scss'

/**
 * StatCard component.
 *
 * @param {Object}             props
 * @param {JSX.Element|string} [props.icon]      WP icon glyph passed to <Icon icon={…}>, or a dashicon class
 *                                               suffix string (e.g. "smiley") rendered as a native dashicon span.
 * @param {string}             props.label       Stat label, e.g. "Active brands".
 * @param {string|number}      props.value       Primary numeric/text value, e.g. "0".
 * @param {string}             [props.tooltip]   Tooltip text for the help icon.
 * @param {string}             [props.className] Extra class(es) for the card root. Use to set
 *                                               - -stat-icon-bg / --stat-icon-color CSS custom
 *                                               properties for per-card icon colours.
 * @param {string|JSX.Element} [props.sub]       Optional sub-label rendered below the value (e.g. a date string).
 * @param {boolean}            [props.compact]   Reduce card padding to 20px (adds --compact modifier).
 * @param {Object}             [props.analysis]  Optional analysis object. If provided, the card will apply
 *
 * @return {JSX.Element} Stat card with icon, label, value, and optional trend.
 */
const StatCard = ( { icon, label, value, tooltip, sub, compact = false, className = '', analysis = null } ) => {
	const hasData = ( analysis?.status && analysis?.status === 'done' ) ?? ( value !== null && value !== undefined && value !== '' && value !== '—' )

	const ns = 'rank-math-ai-visibility-stat-card'

	const rootClass = [
		ns,
		hasData && `${ ns }--has-data`,
		compact && `${ ns }--compact`,
		className,
	].filter( Boolean ).join( ' ' )

	const labelClass = `${ ns }__label`
	const valueClass = `${ ns }__value`

	return (
		<div className={ rootClass }>

			{ icon && (
				<span className={ `${ ns }__icon-wrap` }>
					{ typeof icon === 'string' ? (
						<span className={ `dashicons dashicons-${ icon } ${ ns }__icon` } />
					) : (
						<Icon
							icon={ icon }
							className={ `${ ns }__icon` }
						/>
					) }
				</span>
			) }

			<div className={ `${ ns }__content` }>

				<div className={ `${ ns }__label-row` }>
					<span className={ labelClass }>{ label }</span>
					{ tooltip && HelpTooltip && (
						<HelpTooltip text={ tooltip } />
					) }
				</div>

				<div className={ `${ ns }__data` }>
					<span className={ valueClass }>{ value !== null && value !== undefined ? value : 'N/A' }</span>

					{ sub && (
						<span className={ `${ ns }__sub` }>{ sub }</span>
					) }
				</div>

			</div>

		</div>
	)
}

export default StatCard
