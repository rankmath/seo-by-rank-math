/* eslint-disable eslint-comments/disable-enable-pair */
/* eslint-disable @wordpress/no-global-event-listener */

import jQuery from 'jquery'
import debounce from 'lodash/debounce'

const cssVars = {
	init() {
		this.cacheProps()
		this.initVars()
		this.bindEvents()
	},
	cacheProps() {
		this.root = document.documentElement
		this.$wpAdminbar = jQuery( '#wpadminbar' )
	},
	initVars() {
		if ( this.$wpAdminbar.length ) {
			this.setWpAdminbarHeight.call( this )
		}
	},
	bindEvents() {
		window.addEventListener( 'resize', debounce( this.onWindowResize.bind( this ) ) )
	},
	onWindowResize() {
		if ( this.$wpAdminbar.length ) {
			this.setWpAdminbarHeight.call( this )
		}
	},
	setWpAdminbarHeight() {
		this.root.style.setProperty(
			'--rankmath-wp-adminbar-height',
			this.$wpAdminbar.outerHeight() + 'px'
		)
	},
}

jQuery( function() {
	cssVars.init()
} )
