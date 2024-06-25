/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/StatusAnchorTag.scss'

/**
 * Status Anchor Tag component.
 *
 * @param {Object} props           Component props.
 * @param {string} props.className CSS class for additional styling.
 * @param {string} props.children  Child elements to be rendered inside the anchor tag.
 * @param {string} props.href      Specifies link destination.
 * @param {string} props.severity  Determines the severity. Accepted values: 'default', 'good', 'bad', or 'neutral'.
 */
export default ( {
	className,
	children,
	href = '',
	severity = 'default',
	...additionalProps
} ) => {
	className = classNames(
		className,
		`is-${ severity }`,
		'rank-math-status-anchor-tag'
	)

	const props = {
		...additionalProps,
		href,
		children,
		className,
		target: '_blank',
		variant: 'secondary',
		rel: 'noopener noreferrer',
	}

	return <Button { ...props } />
}
