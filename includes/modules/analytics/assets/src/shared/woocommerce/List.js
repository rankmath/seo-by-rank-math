/**
 * External dependencies
 */
import classnames from 'classnames'
import PropTypes from 'prop-types'

/**
 * WordPress dependencies
 */
import { ENTER } from '@wordpress/keycodes'
import { Component } from '@wordpress/element'

/**
 * List component to display a list of items.
 */
class List extends Component {
	handleKeyDown( event, onClick ) {
		if ( typeof onClick === 'function' && event.keyCode === ENTER ) {
			onClick()
		}
	}

	render() {
		const { className, items } = this.props
		const listClassName = classnames( 'rank-math-list', className )

		return (
			<ul
				className={ listClassName }
				role="menu"
			>
				{ items.map( ( item, index ) => {
					const {
						className: itemClasses,
						content,
						key,
						listItemTag,
						onClick,
						title,
					} = item
					const hasAction = typeof onClick === 'function'
					const itemClassName = classnames(
						'rank-math-list__item',
						itemClasses,
						{
							'has-action': hasAction,
						}
					)

					const innerTagProps = {
						className: 'rank-math-list__item-inner',
						onClick: typeof onClick === 'function' ? onClick : null,
						'aria-disabled': hasAction ? 'false' : null,
						tabIndex: hasAction ? '0' : null,
						role: hasAction ? 'menuitem' : null,
						onKeyDown: ( e ) =>
							hasAction ? this.handleKeyDown( e, onClick ) : null,
						'data-list-item-tag': listItemTag,
					}

					return (
						<li className={ itemClassName } key={ key || index }>
							<div { ...innerTagProps }>
								<div className="rank-math-list__item-text">
									<span className="rank-math-list__item-title" title={ title }>
										{ title }
									</span>
									{ content && (
										<span className="rank-math-list__item-content">
											{ content }
										</span>
									) }
								</div>
							</div>
						</li>
					)
				} ) }
			</ul>
		)
	}
}

List.propTypes = {
	/**
	 * Additional class name to style the component.
	 */
	className: PropTypes.string,
	/**
	 * An array of list items.
	 */
	items: PropTypes.arrayOf(
		PropTypes.shape( {
			/**
			 * Additional class name to style the list item.
			 */
			className: PropTypes.string,
			/**
			 * Content displayed beneath the list item title.
			 */
			content: PropTypes.oneOfType( [
				PropTypes.string,
				PropTypes.node,
			] ),
			/**
			 * Called when the list item is clicked.
			 */
			onClick: PropTypes.func,
			/**
			 * Title displayed for the list item.
			 */
			title: PropTypes.oneOfType( [ PropTypes.string, PropTypes.node ] ),
		} )
	).isRequired,
}

export default List
