/**
 * External dependencies
 */
import jQuery from 'jquery'
import { get } from 'lodash'

class UIThemeComponent {
	constructor() {
		this.onThemeChange = this.onThemeChange.bind( this )
		elementor.settings.editorPreferences.model.on(
			'change',
			this.onThemeChange
		)
	}

	/**
	 * On theme change.
	 *
	 * @param {Object} event Event object.
	 *
	 * @return {void}
	 */
	onThemeChange( event ) {
		const mode = get( event, 'changed.ui_theme', false )
		if ( false === mode ) {
			return
		}

		const link = this.getThemeLink()
		if ( 'light' === mode ) {
			link.remove()
			return
		}

		link.attr(
			'media',
			'auto' === mode ? '(prefers-color-scheme: dark)' : ''
		).appendTo( elementorCommon.elements.$body )
	}

	/**
	 * Function to get dark mode CSS link.
	 *
	 * @return {string} url Dark Mode CSS link
	 */
	getThemeLink() {
		if ( ! this.link ) {
			this.createThemeLink()
		}

		return this.link
	}

	/**
	 * Function to create link tag to support Elementor Dark mode.
	 *
	 * @return {void}
	 */
	createThemeLink() {
		const darkModeLinkID = 'rank-math-elementor-dark-css'

		this.link = jQuery( '#' + darkModeLinkID )

		if ( ! this.link.length ) {
			this.link = jQuery( '<link>', {
				id: darkModeLinkID,
				rel: 'stylesheet',
				href: rankMath.elementorDarkMode,
			} )
		}
	}
}

export default UIThemeComponent
