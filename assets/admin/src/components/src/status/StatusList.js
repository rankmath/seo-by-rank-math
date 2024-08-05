/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * Internal dependencies
 */
import icons from '../icons'
import './scss/StatusList.scss'

/**
 * Status List component.
 *
 * @param {Object} props             Component props.
 * @param {string} props.status      Specifies the control's style. Accepted values: 'good', 'bad', or 'neutral'.
 * @param {string} props.href        Status link destination.
 * @param {string} props.className   CSS classname for custom styling.
 * @param {Object} props.description Status description.
 */
export default ( { status = 'good', href, className, description, ...additionalProps } ) => {
	className = classNames( className, `is-${ status }`, 'rank-math-status-list' )

	return (
		<div { ...additionalProps } className={ className }>
			<span className="rank-math-status-list__icon">
				{ icons.status[ status ] }
			</span>

			<div className="rank-math-status-list__description">
				{ description }
				{ ' ' }
				<a href={ href } target="_blank" rel="noopener noreferrer">
					{ icons.help }
				</a>
			</div>
		</div>
	)
}
