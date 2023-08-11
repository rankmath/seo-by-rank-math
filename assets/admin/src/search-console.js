/* global confirm, alert */

/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { get, map, isUndefined } from 'lodash'

/**
 * Internal Dependencies
 */
import ajax from '@helpers/ajax'
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

class SearchConsole {
	/**
	 * Response.
	 */
	response = ''

	checkAll() {
		const checkAll = jQuery( '#setting-panel-analytics .cmb2-id-check-all-services:not(.done), #cmb2-metabox-rank-math-wizard .cmb2-id-check-all-services:not(.done)' )
		if ( checkAll.length > 0 && '0' === checkAll.val() ) {
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
		this.countryConsole = jQuery( '#site-console-country' )
		this.countryAnalytics = jQuery( '#site-analytics-country' )
		this.testConnectionButton = jQuery( '.rank-math-test-connection-google' )

		jQuery( '.cmb2_select' ).on( 'select2:open', function() {
			document.querySelector( '.select2-search__field' ).focus()
		} )

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

		this.profileSelect.on( 'change', () => {
			if ( 0 !== parseInt( this.profileSelect.val() ) ) {
				document.getElementById( 'enable-index-status' ).removeAttribute( 'disabled' )
			}
		} )

		const submitSelectors = [
			'.rank-math-wizard-body--analytics .form-footer.rank-math-ui .button-primary',
			'.rank-math_page_rank-math-options-general .form-footer.rank-math-ui .button-primary',
		]
		jQuery( submitSelectors.join( ', ' ) ).on( 'click', ( e ) => {
			this.submitButtonHandler( e )
		} )

		this.testConnectionButton.on( 'click', ( e ) => {
			e.preventDefault()
			this.testConnections( e )
		} )

		this.propertySelect.on( 'change', () => {
			if ( 'create-ga4-property' === this.propertySelect.val() ) {
				this.createNewProperty()
				return
			}

			this.response.type = get(
				this.response.accounts,
				[ this.accountSelect.val(), 'properties', this.propertySelect.val(), 'type' ],
				{}
			)

			if ( 'GA4' !== this.response.type ) {
				this.response.type = 'GA3'

				this.response.views = get(
					this.response.accounts,
					[ this.accountSelect.val(), 'properties', this.propertySelect.val(), 'profiles' ],
					{}
				)
			}

			if ( 'GA4' !== this.response.type ) {
				this.fillViewSelect()
				return
			}

			this.createNewDataStream()
		} )

		this.viewSelect.on( 'change', ( e ) => {
			const selected = jQuery( e.target ).find( ':selected' )
			if ( selected.data( 'measurement-id' ) ) {
				jQuery( '#rank-math-analytics-measurement-id' ).val( selected.data( 'measurement-id' ) )
				jQuery( '#rank-math-analytics-stream-name' ).val( selected.text() )
			}
		} )

		jQuery( '.rank-math-disconnect-google' ).on( 'click', ( event ) => {
			event.preventDefault()

			if ( confirm( rankMath.confirmDisconnect ) ) {
				ajax( 'disconnect_google' ).done( () => {
					window.location.reload()
				} )
			}
		} )
	}

	createNewProperty() {
		if ( confirm( __( 'Are you sure, you want to create a new GA4 Property?', 'rank-math' ) ) ) {
			ajax(
				'create_ga4_property',
				{
					accountID: this.accountSelect.val(),
				},
				'post'
			).done( ( response ) => {
				if ( response.error ) {
					this.propertySelect.val( this.propertySelect.find( 'option:first' ).val() )
					alert( response.error )
					return
				}

				this.propertySelect.append(
					'<option value="' + response.id + '">' + response.name + '</option>'
				)

				this.propertySelect.val( response.id )
				this.createNewDataStream()
				this.response.type = 'GA4'
			} )
		} else {
			this.propertySelect.val( this.propertySelect.find( 'option:first' ).val() )
		}
	}

	createNewDataStream() {
		this.viewSelect.html( '' )
		this.viewSelect.prop( 'disabled', true )
		ajax(
			'get_ga4_data_streams',
			{
				propertyID: this.propertySelect.val(),
			},
			'post'
		).done( ( response ) => {
			if ( response.error ) {
				console.error( response.error )
				return
			}

			this.response.views = response.streams
			this.fillViewSelect()
			this.viewSelect.trigger( 'change' )
		} )
	}

	async submitButtonHandler( e ) {
		const target = jQuery( e.target )
		e.preventDefault()

		if ( target.hasClass( 'disabled' ) ) {
			return
		}

		if ( ! jQuery( '#setting-panel-analytics:visible' ).length || jQuery( '#setting-panel-analytics .connect-wrap' ).length ) {
			target.off( 'click' ).trigger( 'click' )
			return
		}

		target.addClass( 'disabled' ).val( __( 'Saving…', 'rank-math' ) )

		const consoleConnected = await this.saveConsole()
		const analyticsConnected = await this.saveAnalytics()
		const adsenseConnected = await this.saveAdsense()

		// Remove all notices.
		jQuery( '.rank-math-accordion' ).find( '.rank-math-notice' ).remove()

		let content = ''
		let error = ''

		if ( ! consoleConnected.success ) {
			content = jQuery( '.rank-math-connect-search-console .rank-math-accordion-content' )
			error = consoleConnected.error
		} else if ( ! analyticsConnected.success ) {
			content = jQuery( '.rank-math-connect-analytics .rank-math-accordion-content' )
			error = analyticsConnected.error
		} else if ( ! adsenseConnected.success ) {
			content = jQuery( '.rank-math-connect-adsense .rank-math-accordion-content' )
			error = adsenseConnected.error
		}

		// Have anyone connection issue?
		if ( ! consoleConnected.success || ! analyticsConnected.success || ! adsenseConnected.success ) {
			content.append( '<div class="rank-math-notice notice notice-error"><p>' + error + '</p></div>' )

			jQuery( 'html, body' ).animate( {
				scrollTop: content.offset().top,
			}, 2000 )

			target.removeClass( 'disabled' ).val( __( 'Save Changes', 'rank-math' ) )
		} else {
			target.off( 'click' ).trigger( 'click' )
		}
	}

	testConnections( e ) {
		e.preventDefault()

		const tests = applyFilters(
			'rank_math_test_connections',
			[
				{
					class: '.rank-math-connect-search-console',
					canTest: rankMath.isConsoleConnected,
					action: 'check_console_request',
				},
				{
					class: '.rank-math-connect-analytics',
					canTest: rankMath.isAnalyticsConnected,
					action: 'check_analytics_request',
				},
			],
			this
		)

		tests.forEach( ( test ) => {
			if ( test.canTest ) {
				const parent = jQuery( test.class )
				const wrap = parent.find( '.rank-math-connection-status-wrap' )

				wrap.html( '<svg class="rank-math-spinner" viewBox="0 0 100 100" width="16" height="16" xmlns="http://www.w3.org/2000/svg" role="presentation" focusable="false"><circle cx="50" cy="50" r="50" vector-effect="non-scaling-stroke"></circle><path d="m 50 0 a 50 50 0 0 1 50 50" vector-effect="non-scaling-stroke"></path></svg>' )

				ajax( test.action, {}, 'post' ).done( ( response ) => {
					if ( response.success ) {
						wrap.html( '<span class="rank-math-connection-status rank-math-connection-status-success" title="' + __( 'Connected', 'rank-math' ) + '"></span>' )
					} else {
						wrap.html( '<span class="rank-math-connection-status rank-math-connection-status-error" title="' + __( 'Some permissions are missing, please reconnect', 'rank-math' ) + '"></span>' )
					}
				} )
			}
		} )
	}

	saveConsole() {
		if ( 0 === parseInt( this.profileSelect.val() ) ) {
			return {
				success: true,
			}
		}

		const data = {
			profile: this.profileSelect.val(),
			country: this.countryConsole.val(),
			enableIndexStatus: jQuery( '#enable-index-status' ).is( ':checked' ),
		}

		const days = jQuery( '#console_caching_control' )
		if ( days.length > 0 ) {
			data.days = days.val()
		}

		return ajax( 'save_analytic_profile', data, 'post' ).done( ( response ) => {
			return response
		} )
	}

	saveAnalytics() {
		const data = {
			accountID: this.accountSelect.val(),
			propertyID: this.propertySelect.val(),
			viewID: this.viewSelect.val(),
			country: this.countryAnalytics.val(),
			installCode: jQuery( '#install-code' ).is( ':checked' ),
			anonymizeIP: jQuery( '#anonymize-ip' ).is( ':checked' ),
			localGAJS: jQuery( '#local-ga-js' ).is( ':checked' ),
			excludeLoggedin: jQuery( '#exclude-loggedin' ).is( ':checked' ),
			measurementID: jQuery( '#rank-math-analytics-measurement-id' ).val(),
			streamName: jQuery( '#rank-math-analytics-stream-name' ).val(),
		}

		if (
			0 === parseInt( data.accountID ) ||
			0 === parseInt( data.propertyID )
		) {
			return {
				success: true,
			}
		}

		const days = jQuery( '#console_caching_control' )
		if ( days.length > 0 ) {
			data.days = days.val()
		}

		return ajax( 'save_analytic_options', data, 'post' ).done( ( response ) => {
			return response
		} )
	}

	saveAdsense() {
		const data = {
			accountID: this.adsenseSelect.val(),
		}

		if ( ! data.accountID ) {
			return {
				success: true,
			}
		}

		return ajax( 'save_adsense_account', data, 'post' ).done( ( response ) => {
			return response
		} )
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
		const { adsenseAccounts = false } = this.response

		if ( false === adsenseAccounts ) {
			return
		}

		map( adsenseAccounts, ( account, id ) => {
			this.adsenseSelect.append(
				'<option value="' + id + '">' + account.name + ' (' + id + ')</option>'
			)
		} )

		if ( this.adsenseSelect.data( 'selected' ) ) {
			this.adsenseSelect.val( this.adsenseSelect.data( 'selected' ) )
		}

		this.adsenseSelect.prop( 'disabled', false )
		this.adsenseSelect.select2()
	}

	fillAccountSelect() {
		const { accounts } = this.response

		map( accounts, ( account, id ) => {
			this.accountSelect.append(
				'<option value="' + id + '">' + account.name + ' (' + id + ')</option>'
			)
		} )

		if ( this.accountSelect.data( 'selected' ) ) {
			this.accountSelect.val( this.accountSelect.data( 'selected' ) )
		} else {
			this.accountSelect.prop( 'disabled', false )
			this.countryAnalytics.prop( 'disabled', false )
			this.accountSelect.select2()
			this.countryAnalytics.select2()
		}

		this.accountSelect.trigger( 'change' )
	}

	fillPropertySelect( account ) {
		const { accounts, homeUrl } = this.response

		const { properties } = accounts[ account ]
		this.propertySelect.html( '<option value="0">Select Property</option>' )
		this.propertySelect.append(
			'<option value="create-ga4-property">' + __( 'Create new GA4 Property', 'rank-math' ) + '</option>'
		)

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
				' (' +
				property.id +
				')</option>'
			)
		} )

		if ( this.propertySelect.data( 'selected' ) ) {
			this.propertySelect.val( this.propertySelect.data( 'selected' ) )
		} else {
			this.propertySelect.prop( 'disabled', false )
			this.propertySelect.select2()
		}

		this.propertySelect.trigger( 'change' )
	}

	fillProfileSelect() {
		const { sites, homeUrl } = this.response

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

		if ( this.profileSelect.data( 'selected' ) ) {
			const wrapper = this.profileSelect.closest( '.rank-math-accordion' )
			wrapper.addClass( 'connected' )
		}

		this.profileSelect.prop( 'disabled', false )
		this.countryConsole.prop( 'disabled', false )
		this.profileSelect.select2()
		this.countryConsole.select2()
	}

	fillViewSelect() {
		const { views, type } = this.response
		const label = 'GA4' === type ? __( 'Data Stream', 'rank-math' ) : __( 'View', 'rank-math' )
		this.viewSelect.prev( 'label' ).text( label )
		map( views, ( view ) => {
			const measurementId = ! isUndefined( view.measurementId ) ? view.measurementId : ''
			this.viewSelect.append(
				'<option value="' + view.id + '" data-measurement-id="' + measurementId + '">' + view.name + '</option>'
			)
		} )

		if ( this.viewSelect.data( 'selected' ) ) {
			this.viewSelect.val( this.viewSelect.data( 'selected' ) )
		}
		this.viewSelect.prop( 'disabled', false )

		this.viewSelect.select2()
	}
}

export const searchConsole = new SearchConsole()
