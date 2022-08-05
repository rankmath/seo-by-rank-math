/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { dispatch, withSelect } from '@wordpress/data'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import ModalButton from './ModalButton'

const DiscardIcon = () => (
	<div
		className="et-fb-icon et-fb-icon--close"
		style={ {
			fill: 'rgb(255, 255, 255)',
			width: '28px',
			minWidth: '28px',
			height: '28px',
			margin: '-6px',
		} }
	>
		<svg
			viewBox="0 0 28 28"
			preserveAspectRatio="xMidYMid meet"
			shapeRendering="geometricPrecision"
		>
			<g>
				<path
					d="M15.59 14l4.08-4.082a1.124 1.124 0 0 0-1.587-1.588L14 12.411 9.918 8.329A1.124 1.124 0 0 0 8.33 9.92L12.411 14l-4.082 4.082a1.124 1.124 0 0 0 1.59 1.589L14 15.589l4.082 4.082a1.124 1.124 0 0 0 1.589-1.59L15.589 14h.001z"
					fillRule="evenodd"
				/>
			</g>
		</svg>
	</div>
)

const HeaderDiscardButton = () => {
	const classes = classNames(
		'rank-math-rm-modal-header-discard-button'
	)
	const onClick = () => {
		dispatch( 'rank-math' ).toggleIsDiviRankMathModalActive( false )
	}
	return (
		<ModalButton
			className={ classes }
			onClick={ onClick }
		>
			<DiscardIcon />
		</ModalButton>
	)
}

const Header = () => {
	const classes = classNames(
		'rank-math-rm-modal-header'
	)
	return (
		<header className={ classes }>
			<div className="rank-math-rm-modal-header">
				<div className="rank-math-rm-modal-header-title">
					Rank Math SEO
				</div>
				<ul className="rank-math-rm-modal-header-options">
					<li className="rank-math-rm-modal-header-option">
						<HeaderDiscardButton />
					</li>
				</ul>
			</div>
		</header>
	)
}

const Content = ( { children } ) => {
	const classes = classNames(
		'rank-math-rm-modal-content',
		'rank-math-sidebar-panel'
	)
	return (
		<div className={ classes }>
			{ children }
		</div>
	)
}

const Modal = ( { rmUiActive } ) => {
	const classes = classNames(
		'rank-math-rm-modal',
		{ 'rank-math-rm-modal-is-hidden': ! rmUiActive }
	)
	const innerClasses = classNames(
		'rank-math-rm-modal-inner'
	)
	return (
		<div className={ classes }>
			<div className={ innerClasses }>
				<Header />
				<Content>
					{
						/* Filter to include components from the common editor file */
						applyFilters( 'rank_math_app', {} )()
					}
				</Content>
			</div>
		</div>
	)
}

export default withSelect( ( select ) => {
	const {
		isDiviRankMathModalActive,
	} = select( 'rank-math' )
	return {
		rmUiActive: isDiviRankMathModalActive(),
	}
} )( Modal )
