/* globals MutationObserver */

/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data'
import { createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import SettingsBar from './components/SettingsBar'
import LockModifiedDate from './components/LockModifiedDate'

export default {
	init() {
		this.cacheProps()
		this.toggleBodyClasses()
		this.initSettingsBar()
		this.addEventListeners()
		this.initLockModifiedDate()
	},

	cacheProps() {
		this.$document = jQuery( document )
		this.$body = jQuery( 'body' )
		this.publishButton = jQuery( '.et-fb-button--publish' )

		// RankMath
		this.rmModalHiddingTimer = null
		this.rmPrevModalActiveState = false
		this.rmSettingsBarMediaQuery = window.matchMedia( '(min-width: 768px)' )
		this.rmSettingsBarRootSelector = '#rank-math-rm-settings-bar-root'
		this.$rmSettingsBarRoot = jQuery( this.rmSettingsBarRootSelector ).detach()

		// Divi
		this.$etPageSettingsBar = jQuery( '.et-fb-page-settings-bar' )
		this.$etPageSettingsBarToggleButton = this.$etPageSettingsBar.find( '.et-fb-page-settings-bar__toggle-button' )
		this.$etPageSettingsBarColumn = this.$etPageSettingsBar.find( '.et-fb-page-settings-bar__column' )
		this.etSettingsBarObserver = new MutationObserver( this.onEtSettingsBarClassAttrChange.bind( this ) )
	},

	toggleBodyClasses() {
		const active = this.isEtSettingsBarActive()
		this.$body.toggleClass( 'rank-math-et-settings-bar-is-active', active )
		this.$body.toggleClass( 'rank-math-et-settings-bar-is-inactive', ! active )
	},

	initSettingsBar() {
		const position = this.getEtSettingsBarPosition()
		this.onRmSettingsBarMediaQueryChange()
		createRoot( this.$rmSettingsBarRoot[ 0 ] ).render( <SettingsBar /> )
		this.removePositionalClassNames( this.$body, 'rank-math-et-settings-bar-is' )
		this.$body.addClass( `rank-math-et-settings-bar-is-${ position }` )
		this.attachRmSettingsBar( position )
	},

	initLockModifiedDate() {
		if ( ! rankMath.showLockModifiedDate ) {
			return
		}

		this.publishButton.after( '<div id="rank-math-lock-modified-date-wrapper"></div>' )
		createRoot( document.getElementById( 'rank-math-lock-modified-date-wrapper' ) ).render( <LockModifiedDate publishButton={ this.publishButton } /> )
	},

	addEventListeners() {
		this.$document.on( 'click', this.onDocumentClick.bind( this ) )
		this.rmSettingsBarMediaQuery.addListener(
			this.onRmSettingsBarMediaQueryChange.bind( this )
		)
		this.etSettingsBarObserver.observe(
			this.$etPageSettingsBar[ 0 ],
			{ attributeFilter: [ 'class' ] }
		)
	},

	onDocumentClick( e ) {
		this.hideModalOnOutsideClick( e.target )
	},

	onRmSettingsBarMediaQueryChange() {
		this.detachRmSettingsBar()
		this.attachRmSettingsBar( this.getEtSettingsBarPosition() )
	},

	/**
	 * On ET-Settingsbar class attribute change.
	 *
	 * Watch `$etPageSettingsBar` for class attribute changes to determine
	 * whether it's active and what its current position is.
	 */
	onEtSettingsBarClassAttrChange() {
		const active = this.isEtSettingsBarActive(),
			position = this.getEtSettingsBarPosition()
		this.removePositionalClassNames( this.$body, 'rank-math-et-settings-bar-is' )
		this.$body.addClass( `rank-math-et-settings-bar-is-${ position }` )
		dispatch( 'rank-math' ).toggleIsDiviPageSettingsBarActive( active )
		this.toggleBodyClasses()
		this.detachRmSettingsBar()
		if ( this.isEtSettingsBarDragged() ) {
			this.rmPrevModalActiveState = select( 'rank-math' ).isDiviRankMathModalActive()
			if ( this.rmPrevModalActiveState ) {
				// NOTE: Timeout prevents modal flicker for slightly prolonged mousedown events.
				this.rmModalHiddingTimer = setTimeout( () => {
					dispatch( 'rank-math' ).toggleIsDiviRankMathModalActive( false )
				}, 200 )
			}
		} else {
			clearTimeout( this.rmModalHiddingTimer )
			this.attachRmSettingsBar( position )
			dispatch( 'rank-math' ).toggleIsDiviRankMathModalActive(
				this.rmPrevModalActiveState || select( 'rank-math' ).isDiviRankMathModalActive()
			)
			this.rmPrevModalActiveState = false
		}
	},

	attachRmSettingsBar( position ) {
		if ( this.isRmSettingsBarAttached() ) {
			return
		}
		this.toggleRmSettingsBarClassNames( position )
		if ( this.isEtSettingsBarActive() ) {
			if ( this.rmSettingsBarMediaQuery.matches ) {
				this.$etPageSettingsBarColumn
					.filter( '.et-fb-page-settings-bar__column--main' )
					.append( this.$rmSettingsBarRoot )
			} else {
				this.$etPageSettingsBarColumn
					.filter( '.et-fb-page-settings-bar__column--left' )
					.prepend( this.$rmSettingsBarRoot )
			}
		} else {
			this.$etPageSettingsBarToggleButton
				.after( this.$rmSettingsBarRoot )
		}
	},

	detachRmSettingsBar() {
		if ( ! this.isRmSettingsBarAttached() ) {
			return
		}
		this.$rmSettingsBarRoot = this.$etPageSettingsBar.find(
			this.rmSettingsBarRootSelector
		).detach()
	},

	toggleRmSettingsBarClassNames( position ) {
		this.removePositionalClassNames( this.$rmSettingsBarRoot )
		this.$rmSettingsBarRoot.addClass( `rank-math-rm-settings-bar-root-${ position }` )
		this.$rmSettingsBarRoot.toggleClass(
			[
				'rank-math-rm-settings-bar-root-is-mobile',
				`rank-math-rm-settings-bar-root-is-mobile-${ position }`,
			].join( ' ' ),
			! this.rmSettingsBarMediaQuery.matches
		)
		this.$rmSettingsBarRoot.toggleClass(
			[
				'rank-math-rm-settings-bar-root-is-desktop',
				`rank-math-rm-settings-bar-root-is-desktop-${ position }`,
			].join( ' ' ),
			this.rmSettingsBarMediaQuery.matches
		)
	},

	isRmSettingsBarAttached() {
		return jQuery.contains( document.documentElement, this.$rmSettingsBarRoot[ 0 ] )
	},

	isEtSettingsBarActive() {
		return this.$etPageSettingsBar.hasClass( 'et-fb-page-settings-bar--active' )
	},

	isEtSettingsBarDragged() {
		return this.$etPageSettingsBar.hasClass( 'et-fb-page-settings-bar--dragged' ) &&
			! this.isEtSettingsBarActive()
	},

	removePositionalClassNames( $elem, namespace = '' ) {
		const positionClassNameEndings = [
				`${ namespace }-left`,
				`${ namespace }-right`,
				`${ namespace }-top`,
				`${ namespace }-top-left`,
				`${ namespace }-top-right`,
				`${ namespace }-bottom`,
				`${ namespace }-bottom-left`,
				`${ namespace }-bottom-right`,
			].join( '|' ),
			positionRegex = new RegExp( `(${ positionClassNameEndings })$`, 'gim' )
		$elem.removeClass( ( index, classes ) => {
			const classNames = classes.split( ' ' ),
				positionClassNames = []
			for ( const className of classNames ) {
				if ( positionRegex.test( className ) ) {
					positionClassNames.push( className )
				}
			}
			return positionClassNames
		} )
	},

	getEtSettingsBarPosition() {
		const $b = this.$etPageSettingsBar
		if ( $b.hasClass( 'et-fb-page-settings-bar--horizontal' ) && ! $b.hasClass( 'et-fb-page-settings-bar--top' ) ) {
			return 'bottom'
		} else if ( $b.hasClass( 'et-fb-page-settings-bar--top' ) && ! $b.hasClass( 'et-fb-page-settings-bar--corner' ) ) {
			return 'top'
		} else if ( $b.hasClass( 'et-fb-page-settings-bar--bottom-corner' ) ) {
			return $b.hasClass( 'et-fb-page-settings-bar--left-corner' )
				? 'bottom-left'
				: 'bottom-right'
		} else if ( $b.hasClass( 'et-fb-page-settings-bar--top-corner' ) ) {
			return $b.hasClass( 'et-fb-page-settings-bar--left-corner' )
				? 'top-left'
				: 'top-right'
		} else if ( $b.hasClass( 'et-fb-page-settings-bar--vertical--right' ) ) {
			return 'right'
		} else if ( $b.hasClass( 'et-fb-page-settings-bar--vertical--left' ) ) {
			return 'left'
		}
		return ''
	},

	hideModalOnOutsideClick( elem ) {
		if ( ! select( 'rank-math' ).isDiviRankMathModalActive() ) {
			return
		}

		const seoModalSelector = '.rank-math-rm-modal',
			previewModalSelector = '.components-modal__screen-overlay.rank-math-modal-overlay',
			modalToggle = '.rank-math-rm-modal-toggle-button'

		if (
			! jQuery( elem ).parents( seoModalSelector ) &&
			! elem.closest( previewModalSelector ) &&
			! elem.closest( modalToggle ) &&
			! elem.contains( document.querySelector( seoModalSelector ) )
		) {
			dispatch( 'rank-math' ).toggleIsDiviRankMathModalActive( false )
		}
	},
}
