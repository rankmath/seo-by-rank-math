/**
 * External dependencies
 */
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data'

const ToggleIcon = () => (
	<span
		className="rank-math-rm-modal-toggle-button-icon"
		style={ {
			display: 'block',
			fill: 'rgb(255, 255, 255)',
			width: '0px',
			height: '0px',
			marginTop: '-10px',
			marginLeft: '-1px',
		} }
	>
		<svg
			viewBox="0 0 462.03 462.03"
			xmlns="http://www.w3.org/2000/svg"
			width="20"
		>
			<g>
				<path d="m462 234.84-76.17 3.43 13.43 21-127 81.18-126-52.93-146.26 60.97 10.14 24.34 136.1-56.71 128.57 54 138.69-88.61 13.43 21z"></path>
				<path d="m54.1 312.78 92.18-38.41 4.49 1.89v-54.58h-96.67zm210.9-223.57v235.05l7.26 3 89.43-57.05v-181zm-105.44 190.79 96.67 40.62v-165.19h-96.67z"></path>
			</g>
		</svg>
	</span>
)

const ModalToggleButton = () => {
	const onClick = () => {
		dispatch( 'rank-math' ).toggleIsDiviRankMathModalActive(
			! select( 'rank-math' ).isDiviRankMathModalActive()
		)
	}
	return (
		<button
			type="button"
			data-tip="Rank Math SEO"
			onClick={ onClick }
			className={ classNames(
				'rank-math-rm-modal-toggle-button',
				'et_fb_ignore_iframe',
				'et-fb-button',
				'et-fb-button--elevate',
				'et-fb-button--primary',
				'et-fb-button--round',
				'et-fb-button--Tooltip',
			) }
			style={ {
				width: '40px',
				height: '40px',
			} }
		>
			<ToggleIcon />
		</button>
	)
}

const SettingsBar = () => {
	return (
		<div className="rank-math-rm-settings-bar">
			<ModalToggleButton />
		</div>
	)
}

export default SettingsBar
