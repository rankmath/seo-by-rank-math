/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/Notice.scss'

/**
 * Notice component.
 *
 * @param {Object}   props               Component props.
 * @param {string}   props.icon          If provided, renders an icon inside the notice.
 * @param {string}   props.children      The displayed message of the notice. Also used as the spoken message for assistive technology unless spokenMessage is provided.
 * @param {Function} props.onRemove      Callback invoked to dismiss the notice.
 * @param {string}   props.className     CSS class for additional styling.
 * @param {string}   props.status        Determines the notice color. Accepted values: 'warning', 'success', 'error', or 'info'.
 * @param {Array}    props.actions       An array of action objects.
 * @param {string}   props.politeness    A politeness level for the notice's spoken message.
 * @param {boolean}  props.isDismissible Whether the notice should be dismissible or not.
 */
export default ( {
	icon,
	children,
	onRemove,
	className,
	status = 'info',
	actions = [],
	politeness = 'polite',
	isDismissible = false,
	...additionalProps
} ) => {
	className = classNames(
		className,
		'rank-math-notice',
		{
			'has-icon': icon,
		}
	)

	const props = {
		...additionalProps,
		status,
		actions,
		onRemove,
		className,
		politeness,
		isDismissible,
	}

	return (
		<Notice { ...props }>
			{ icon && (
				<span className="rank-math-notice__icon">
					{ icon }
				</span>
			) }

			{ children }
		</Notice>
	)
}
