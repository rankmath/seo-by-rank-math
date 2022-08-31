/**
 * External dependencies
 */
import jQuery from 'jquery'
import { get, forEach } from 'lodash'

class UIThemeComponent {
	/**
	 * Links
	 *
	 * @type {Object}
	 */
	links = {}

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

		forEach( rankMath.elementorDarkMode, ( url, key ) => {
			const link = this.getLink( key + '-css', url )
			if ( 'light' === mode ) {
				link.remove()
				return
			}

			link.attr( 'media', 'auto' === mode ? '(prefers-color-scheme: dark)' : '' ).appendTo( elementorCommon.elements.$body )
		} )
	}

	/**
	 * Function to get dark mode CSS link.
	 *
	 * @param {string} key The dark mode key
	 * @param {string} url The dark mode URL
	 * @return {string} url Dark Mode CSS link
	 */
	getLink( key, url ) {
		if ( ! this.links[ key ] ) {
			this.createLink( key, url )
		}

		return this.links[ key ]
	}

	/**
	 * Function to create link tag to support Elementor Dark mode.
	 *
	 * @param {string} key The dark mode key
	 * @param {string} url The dark mode URL
	 * @return {void}
	 */
	createLink( key, url ) {
		this.links[ key ] = jQuery( '#' + key ).length ? jQuery( '#' + key ) : null

		if ( ! this.links[ key ] ) {
			this.links[ key ] = jQuery( '<link>', {
				id: key,
				rel: 'stylesheet',
				href: url,
			} )
		}
	}
}

export default UIThemeComponent
