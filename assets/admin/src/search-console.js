/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { get, map } from 'lodash'

/**
 * Internal Dependencies
 */
import ajax from '@helpers/ajax'

class SearchConsole {
	/**
	 * Response.
	 */
	response = ''

	checkAll() {
		const checkAll = jQuery( '#setting-panel-analytics .cmb2-id-check-all-services:not(.done), #cmb2-metabox-rank-math-wizard .cmb2-id-check-all-services:not(.done)' )
		if ( checkAll.length > 0 && '0' == checkAll.val() ) {
			checkAll.addClass( 'done' )

			ajax( 'google_check_all_services' ).done( ( response ) => {
				this.response = response
				this.fillSelect()
				this.accordions.removeClass( 'locked' )
			} )
		}
	}

	events() {
		this.saveChanges = false
		this.accountSelect = jQuery( '.site-analytics-account' )
		this.profileSelect = jQuery( '.site-console-profile' )
		this.propertySelect = jQuery( '.site-analytics-property' )
		this.viewSelect = jQuery( '.site-analytics-view' )
		this.adsenseSelect = jQuery( '.site-adsense-account' )
		this.accordions = jQuery( '.rank-math-accordion' )

		this.accountSelect.on( 'change', () => {
			const account = parseInt( this.accountSelect.val() )
			if ( 0 === account ) {
				this.propertySelect.html(
					'<option value="0">Select Property</option>'
				)
				return
			}

			this.fillPropertySelect( account )
		} )

		jQuery( '.button-primary', '.form-footer.rank-math-ui' ).on(
			'click',
			( event ) => {
				if ( this.saveChanges ) {
					event.preventDefault()
					alert( 'There are unsved changes' )
				}
			}
		)

		this.propertySelect.on( 'change', () => {
			const accountID = this.accountSelect.val()
			const propertyID = this.propertySelect.val()
			const { accounts } = this.response

			this.response.views = get(
				accounts,
				[ accountID, 'properties', propertyID, 'profiles' ],
				{}
			)
			this.fillViewSelect()
		} )

		this.accordions.on( 'click', 'header', ( event ) => {
			if ( this.saveChanges ) {
				return
			}

			const current = jQuery( event.currentTarget )
			const parent = current.closest( '.rank-math-accordion' )
			const isCurrentOpen = parent.hasClass( 'is-open' )

			current.next().slideToggle()
			this.accordions
				.not( parent )
				.find( '.rank-math-accordion-close' )
				.trigger( 'click' )
			this.accordions.removeClass( 'is-open' )
			if ( isCurrentOpen ) {
				return
			}

			parent.addClass( 'is-open' )
			this.accordions.not( parent )
		} )

		jQuery( '.rank-math-accordion-close' ).on( 'click', ( event ) => {
			event.preventDefault()

			if ( this.saveChanges ) {
				return
			}

			const button = jQuery( event.currentTarget )
			const container = button.closest( '.rank-math-accordion-content' )

			this.accordions.removeClass( 'is-open locked' )
			container.slideUp()
		} )

		jQuery( 'select,input', '.rank-math-accordion' ).on(
			'change',
			( event ) => {
				this.saveChanges = true
				const parent = jQuery( event.currentTarget ).closest(
					'.rank-math-accordion'
				)
				this.accordions.not( parent ).addClass( 'locked' )

				return true
			}
		)

		jQuery( '.rank-math-save-profiles' ).on( 'click', ( event ) => {
			event.preventDefault()

			if ( 0 === parseInt( this.profileSelect.val() ) ) {
				return
			}

			const button = jQuery( event.currentTarget )
			const parent = button.closest( '.rank-math-accordion' )
			parent.addClass( 'saving' )
			button.text( 'Saving...' )

			const data = {
				profile: this.profileSelect.val(),
				country: jQuery( '.site-console-country' ).val(),
			}

			const days = jQuery( '#console_caching_control' )
			if ( days.length > 0 ) {
				data.days = days.val()
			}

			ajax( 'save_analytic_profile', data, 'post' )
				.complete( () => {
					button.text( 'Saved' )
					parent.removeClass( 'saving' )
				} )
				.done( () => {
					this.removeLock( parent )
					setTimeout( () => {
						button.text( 'Save' )
					}, 1500 )
				} )
		} )

		jQuery( '.rank-math-save-analytics' ).on( 'click', ( event ) => {
			event.preventDefault()
			const data = {
				accountID: this.accountSelect.val(),
				propertyID: this.propertySelect.val(),
				viewID: this.viewSelect.val(),
				country: jQuery( '.site-analytics-country' ).val(),
				installCode: jQuery( '#install-code' ).is( ':checked' ),
				anonymizeIP: jQuery( '#anonymize-ip' ).is( ':checked' ),
				excludeLoggedin: jQuery( '#exclude-loggedin' ).is( ':checked' ),
			}

			if (
				0 === parseInt( data.accountID ) ||
				0 === parseInt( data.propertyID )
			) {
				return
			}

			const days = jQuery( '#console_caching_control' )
			if ( days.length > 0 ) {
				data.days = days.val()
			}

			const button = jQuery( event.currentTarget )
			const parent = button.closest( '.rank-math-accordion' )
			parent.addClass( 'saving' )
			button.text( 'Saving' )

			ajax( 'save_analytic_options', data, 'post' )
				.complete( () => {
					button.text( 'Saved' )
					parent.removeClass( 'saving' )
				} )
				.done( () => {
					this.removeLock( parent )
					setTimeout( () => {
						button.text( 'Save' )
					}, 1500 )
				} )
		} )

		jQuery( '.rank-math-save-adsense' ).on( 'click', ( event ) => {
			event.preventDefault()
			const data = {
				accountID: this.adsenseSelect.val(),
			}

			if ( 0 === parseInt( data.accountID ) ) {
				return
			}

			const button = jQuery( event.currentTarget )
			const parent = button.closest( '.rank-math-accordion' )
			parent.addClass( 'saving' )
			button.text( 'Saving' )

			ajax( 'save_adsense_account', data, 'post' )
				.complete( () => {
					button.text( 'Saved' )
					parent.removeClass( 'saving' )
				} )
				.done( () => {
					this.removeLock( parent )
					setTimeout( () => {
						button.text( 'Save' )
					}, 1500 )
				} )
		} )

		jQuery( '.rank-math-disconnect-google' ).on( 'click', ( event) => {
			event.preventDefault()

			if ( confirm( 'Are you sure?' ) ) {
				ajax( 'disconnect_google' ).done( () => {
					window.location.reload()
				} )
			}
		} )
	}

	removeLock( parent ) {
		this.saveChanges = false
		this.accordions.removeClass( 'locked' )
		parent.addClass( 'connected' )
		parent.removeClass( 'disconnected' )
	}

	fillSelect() {
		const { inSearchConsole, isVerified } = this.response

		this.fillProfileSelect()
		this.fillAccountSelect()
		this.fillAdsenseSelect()

		if ( ! inSearchConsole ) {
			ajax( 'add_site_console' ).done( ( response ) => {
				this.response.sites = response.sites
				this.fillProfileSelect()
			} )
		}

		if ( inSearchConsole && ! isVerified ) {
			ajax( 'verify_site_console' )
		}
		this.saveChanges = false
	}

	fillAdsenseSelect() {
		const { adsenseAccounts } = this.response

		this.adsenseSelect.html( '<option value="0">Select Account</option>' )

		map( adsenseAccounts, ( account, id ) => {
			this.adsenseSelect.append(
				'<option value="' + id + '">' + account.name + '</option>'
			)
		} )

		if ( this.adsenseSelect.data( 'selected' ) ) {
			this.adsenseSelect.val( this.adsenseSelect.data( 'selected' ) )
		}

		this.adsenseSelect.prop( 'disabled', false )
	}

	fillAccountSelect() {
		const { accounts } = this.response

		this.accountSelect.html( '<option value="0">Select Account</option>' )

		map( accounts, ( account, id ) => {
			this.accountSelect.append(
				'<option value="' + id + '">' + account.name + '</option>'
			)
		} )

		if ( this.accountSelect.data( 'selected' ) ) {
			this.accountSelect.val( this.accountSelect.data( 'selected' ) )
		} else {
			this.accountSelect.prop( 'disabled', false )
		}

		this.accountSelect.trigger( 'change' )
	}

	fillPropertySelect( account ) {
		const { accounts, homeUrl } = this.response

		const { properties } = accounts[ account ]
		this.propertySelect.html( '<option value="0">Select Property</option>' )

		map( properties, ( property ) => {
			const selected =
				property.url === homeUrl ? ' selected="selected"' : ''
			this.propertySelect.append(
				'<option' +
					selected +
					' value="' +
					property.id +
					'">' +
					property.name +
					'</option>'
			)
		} )

		if ( this.propertySelect.data( 'selected' ) ) {
			this.propertySelect.val( this.propertySelect.data( 'selected' ) )
		} else {
			this.propertySelect.prop( 'disabled', false )
		}

		this.propertySelect.trigger( 'change' )
	}

	fillProfileSelect() {
		const { sites, homeUrl, inSearchConsole } = this.response

		this.profileSelect.html( '<option value="0">Select Profile</option>' )

		let selected = false
		map( sites, ( val, key ) => {
			selected = key === homeUrl ? ' selected="selected"' : ''
			this.profileSelect.append(
				'<option' +
					selected +
					' value="' +
					key +
					'">' +
					val +
					'</option>'
			)
		} )

		if ( ! this.viewSelect.data( 'selected' ) && inSearchConsole ) {
			const wrapper = this.profileSelect.closest( '.rank-math-accordion' )
			wrapper.addClass( 'connected' )
		}

		this.profileSelect.prop( 'disabled', false )
	}

	fillViewSelect() {
		this.viewSelect.html( '<option value="0">Select Web View</option>' )
		const { views } = this.response
		map( views, ( view ) => {
			this.viewSelect.append(
				'<option value="' + view.id + '">' + view.name + '</option>'
			)
		} )

		if ( this.viewSelect.data( 'selected' ) ) {
			this.viewSelect.val( this.viewSelect.data( 'selected' ) )
		}

		this.viewSelect.prop( 'disabled', false )
	}
}

export const searchConsole = new SearchConsole()
