/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { Popover } from '@wordpress/components'
import { ReactNode, useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import './scss/Tooltip.scss'

/**
 * Tooltip component.
 *
 * @param {Object}    props           Component props.
 * @param {string}    props.text      The text shown in the tooltip.
 * @param {ReactNode} props.children  The anchor for the tooltip.
 * @param {string}    props.className CSS class for additional styling.
 * @param {boolean}   props.isBlock   Changes the element from inline to block.
 * @param {string}    props.placement Where the tooltip should be positioned relative to its parent.
 */
export default ( {
	text,
	children,
	className,
	isBlock = false,
	placement = 'top',
} ) => {
	const [ isVisible, setIsVisible ] = useState( false )

	className = classNames( className, 'rank-math-tooltip-container', {
		'is-inline': isBlock,
	} )

	const popoverClasses = classNames( placement, 'rank-math-tooltip-popover', {
		'is-visible': isVisible,
	} )

	// Show or hide the tooltip
	const toggleVisibility = () => {
		setIsVisible( ( prev ) => ! prev )
	}

	return (
		<div className={ className }>
			<div
				onMouseEnter={ toggleVisibility }
				onMouseLeave={ toggleVisibility }
			>
				{ children }
			</div>

			{ text && (
				<Popover
					offset={ 8 }
					shift={ true }
					noArrow={ false }
					placement={ placement }
					className={ popoverClasses }
				>
					{ text }
				</Popover>
			) }
		</div>
	)
}
