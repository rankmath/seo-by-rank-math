/*!
 * Rank Math
 *
 * @version 0.9.0
 * @author  RankMath
 */

/**
 * External Dependencies
 */
import jQuery from 'jquery'

/**
 * Internal Dependencies
 */
import ajax from '@helpers/ajax'
import addNotice from '@helpers/addNotice'

/*eslint no-alert: 0*/
;( function( $ ) {
	// Document Ready
	$( function() {
		window.rankMathOptions = {
			init() {
				this.misc()
				this.preview()
				this.scCache()
				rankMathAdmin.variableInserter()
				this.searchEngine.init()
				this.addressFormat()
				this.siteMap()
				this.robotsEvents()
			},

			searchEngine: {
				init() {
					this.form = $( '.rank-math-search-options' )
					this.wrap = this.form.closest( '.rank-math-wrap-settings' )
					this.input = this.form.find( 'input' )
					this.tabs = this.wrap.find( '.rank-math-tabs' )
					this.panels = this.wrap.find( '.rank-math-tab' )
					this.ids = [ 'general', 'titles', 'sitemap' ]
					this.indexes = {}

					this.buildIndex()
					this.events()
				},

				events() {
					const self = this,
						tabWrapper = self.tabs.find(
							'>.rank-math-tabs-navigation'
						),
						dropdown = $(
							'<div class="rank-math-search-dropdown"></div>'
						)

					// Append needed html.
					self.tabs
						.find( '>.rank-math-tabs-content' )
						.prepend(
							'<div class="rank-math-setting-search-empty hidden">No results found.</div>'
						)
					self.form.append( dropdown )
					dropdown.hide().empty()

					const searchIndexes = _.debounce( function( query ) {
						self.wrap.addClass( 'searching' )
						self.searchIndexes( query, dropdown )
					}, 300 )

					// Events.
					self.form.on( 'click', '.clear-search', function( event ) {
						event.preventDefault()
						self.input.val( '' )
						self.clearSearch( tabWrapper )
					} )

					this.ids.forEach( function( id ) {
						dropdown.append( self.indexes[ id ] )
					} )

					self.input.on( 'input', function() {
						if ( '' === self.input.val() ) {
							self.clearSearch( tabWrapper, dropdown )
							return false
						}

						searchIndexes( self.input.val().toLowerCase() )
					} )

					dropdown.on( 'click', '.cmb-row', function() {
						const row = $( this )
						const loc =
							window.location.origin +
							window.location.pathname +
							'?page=rank-math-options-' +
							row.data( 'settings-id' ) +
							'#' +
							row.closest( '.dropdown-tab' ).data( 'id' )

						if ( loc === window.location.href ) {
							window.location.reload()
						} else {
							window.location = loc
						}
					} )

					// Hide on body click
					const nots = $(
						'.rank-math-search-options, .rank-math-search-options *, .rank-math-search-dropdown, .rank-math-search-dropdown *'
					)
					$( 'body' ).on( 'click', function( event ) {
						if ( ! $( event.target ).is( nots ) ) {
							dropdown.hide()
						}
					} )
				},

				searchIndexes( query, dropdown ) {
					if ( 1 > query.trim().length ) {
						return
					}

					const rows = dropdown.find( '.cmb-row' )
					let found = 0

					rows.hide().each( function() {
						const row = $( this )
						if (
							row
								.text()
								.trim()
								.toLowerCase()
								.includes( query )
						) {
							row.show()
							++found
						}
					} )
					dropdown.show()
					dropdown.toggleClass( 'empty', 0 === found )
				},

				clearSearch( navigation, dropdown ) {
					dropdown = dropdown || false

					this.wrap.removeClass( 'searching search-no-results' )
					$( '>a.active', navigation ).trigger( 'click' )

					if ( dropdown ) {
						dropdown.hide()
					} else {
						$( '.cmb-row' ).show()
						$(
							'.rank-math-cmb-dependency',
							'.cmb-form, .rank-math-metabox-wrap'
						).each( function() {
							rankMathAdmin.loopDependencies( $( this ) )
						} )
					}
				},

				// Search Index
				buildIndex() {
					const indexVersion = window.localStorage.getItem(
							'rank-math-option-search-index'
						),
						force =
							undefined === indexVersion ||
							indexVersion !== rankMath.version

					this.ids.forEach( function( id ) {
						this.getIndex( id, force )
					}, this )

					if ( force ) {
						window.localStorage.setItem(
							'rank-math-option-search-index',
							rankMath.version
						)
					}
				},

				getIndex( id, force ) {
					const self = this

					if ( ! force ) {
						self.indexes[ id ] = $(
							window.localStorage.getItem(
								'rank-math-option-' + id + '-index'
							)
						)
						return
					}

					$( '<div/>' ).load(
						rankMath.adminurl + '?page=rank-math-options-' + id,
						function( response, status ) {
							if ( 'error' === status ) {
								return
							}
							let tabs = $( response ).find(
								'.rank-math-tabs-content'
							)

							tabs.find( '.rank-math-tab' ).each( function() {
								const tab = $( this )
								tab.removeClass().addClass( 'dropdown-tab' )
								tab.attr( 'data-id', tab.attr( 'id' ) )
								tab.removeAttr( 'id' )
								tab.find( '.rank-math-notice' ).remove()
								tab.find( '.rank-math-desc' ).remove()
							} )

							tabs.find( '.rank-math-tab' )
								.removeClass()
								.addClass( 'dropdown-tab' )
								.removeAttr( 'id' )

							tabs.find( '.cmb-row' ).each( function() {
								const row = $( this )
								row.attr( 'data-settings-id', id )

								if (
									row.hasClass( 'cmb-type-title' ) ||
									row.hasClass( 'cmb-type-notice' ) ||
									row.hasClass( 'rank-math-notice' ) ||
									row.hasClass( 'rank-math-desc' )
								) {
									row.remove()
								}

								row.find( '.cmb-td' )
									.children(
										':not(.cmb2-metabox-description)'
									)
									.remove()

								row.find(
									'label,.cmb2-metabox-description'
								).unwrap()

								row.removeAttr( 'data-fieldtype' )
							} )

							tabs = tabs
								.html()
								.replace( /(\r\n\t|\n|\r\t)/gm, '' )

							self.indexes[ id ] = $( tabs )
							window.localStorage.setItem(
								'rank-math-option-' + id + '-index',
								tabs
							)
						}
					)
				},
			},

			scCache() {
				$( '.console-cache-delete' ).on( 'click', function( event ) {
					event.preventDefault()

					const button = $( this ),
						days = button.data( 'days' ),
						message =
							-1 === days
								? 'You are about to delete your whole Cache. Every dataset older than 90 days is lost forever!'
								: 'You are about to delete your 90 days Cache?'

					if (
						window.confirm(
							message + ' Are you sure you want to continue?'
						)
					) {
						button.prop( 'disabled', true )
						ajax( 'search_console_delete_cache', { days }, 'GET' )
							.always( function() {
								button.prop( 'disabled', false )
							} )
							.done( function( result ) {
								if ( result && result.success ) {
									addNotice(
										'Cache deleted.',
										'success',
										$( 'h1', '.rank-math-wrap-settings' )
									)
									$( '.rank-math-console-db-info' ).remove()
									$(
										'#console-updating-manually-progress'
									).before( result.message )
								}
							} )
					}
				} )

				$( '.console-cache-update-manually' ).on( 'click', function(
					event
				) {
					event.preventDefault()

					const button = $( this ),
						days = $( '#console_caching_control' ).val()

					button.prop( 'disabled', true )
					ajax( 'search_console_get_cache', { days }, 'GET' ).done(
						function( result ) {
							if ( result && result.success ) {
								addNotice(
									result.message,
									'success',
									$( 'h1.page-title' )
								)
							} else {
								addNotice(
									'Unable to update cache due to: ' +
										result.error,
									'error',
									$( 'h1.page-title' )
								)
							}
						}
					)
				} )
			},

			addressFormat() {
				const fields = $(
					'input[type=text], textarea',
					'.rank-math-address-format'
				)

				// Early bail if no field needed on the screen.
				if ( ! fields.length ) {
					return
				}

				// Wrap fields.
				fields.attr( 'autocomplete', 'off' )
				fields.wrap( '<div class="rank-math-variables-wrap"/>' )

				const body = $( 'body' )
				const addressWrap = fields.parent( '.rank-math-variables-wrap' )
				addressWrap.append(
					'<a href="#" class="rank-math-variables-button button button-secondary button-address"><span class="dashicons dashicons-arrow-down-alt2"></span></a>'
				)

				// Add dropdown
				const list = $( '<ul/>' ),
					dropdown = $(
						'<div class="rank-math-variables-dropdown"></div>'
					),
					variables = {
						'{address} {locality}, {region} {postalcode}':
							'(New York, NY 12345)',
						'{address} {postalcode}, {locality} {region}':
							'(New York 12345, NY)',
						'{address} {locality} {postalcode}':
							'(New York NY 12345)',
						'{postalcode} {region} {locality} {address}':
							'(12345 NY New York)',
						'{address} {locality}': '(New York NY)',
					}

				$.each( variables, function( key, value ) {
					list.append(
						'<li data-var="' +
							value +
							'"><strong>' +
							key +
							'</strong></li>'
					)
				} )

				// Append list to body
				dropdown.append( list )
				$( 'rank-math-variables-wrap:eq(0)' ).append( dropdown )

				// Hide on body click
				const nots = $(
					'.rank-math-variables-button, .rank-math-variables-button *, .rank-math-variables-dropdown, .rank-math-variables-dropdown *'
				)
				body.on( 'click', function( event ) {
					if ( ! $( event.target ).is( nots ) ) {
						dropdown.hide()
					}
				} )

				// Trigger button
				const input = dropdown.find( 'input' )
				const lis = dropdown.find( 'li' )
				$( addressWrap ).on(
					'click',
					'.rank-math-variables-button',
					function( event ) {
						event.preventDefault()
						$( this ).after( dropdown )
						lis.show()
						dropdown.show()
						input.val( '' ).focus()
					}
				)

				// Insert Variable
				dropdown.on( 'click', 'li', function( event ) {
					event.preventDefault()
					const $this = $( this ),
						holder = $this
							.closest( '.rank-math-variables-wrap' )
							.find( 'textarea' )

					holder.val( $this.find( 'strong' ).text() )
				} )
			},

			misc() {
				if ( 'undefined' !== typeof jQuery.fn.select2 ) {
					$( '[data-s2-pages]' ).select2( {
						ajax: {
							url:
								rankMath.ajaxurl +
								'?action=rank_math_search_pages',
							data: ( params ) => {
								const query = {
									term: params.term,
									security: rankMath.security,
								}

								return query
							},
							dataType: 'json',
							delay: 250,
						},
						width: '100%',
						minimumInputLength: 3,
					} )
				}

				// .htaccess agreed.
				$( '#htaccess_accept_changes' ).on( 'change', function() {
					$( '#htaccess_content' ).prop( 'readonly', ! this.checked )
				} )

				$( '.reset-options' ).on( 'click', function() {
					if (
						window.confirm(
							'Are you sure? You want to reset settings.'
						)
					) {
						$( window ).off( 'beforeunload' )
						return true
					}

					return false
				} )

				const tabsContainer = $( '.rank-math-tabs' )
				setTimeout( function() {
					window.localStorage.removeItem( tabsContainer.attr( 'id' ) )
				}, 1000 )
				$( '.save-options' ).on( 'click', function() {
					const target = $(
						'> .rank-math-tabs-navigation > a.active',
						tabsContainer
					)
					window.localStorage.setItem(
						tabsContainer.attr( 'id' ),
						target.attr( 'href' )
					)

					$( window ).off( 'beforeunload' )

					return true
				} )

				let saveWarn = false
				$( window ).on( 'load', function() {
					$( '.cmb-form' ).on(
						'change',
						'input, textarea, select',
						function() {
							saveWarn = true
						}
					)
				} )

				$( window ).on( 'beforeunload', function() {
					if ( saveWarn ) {
						return "Are you sure? You didn't finish the form!"
					}
				} )

				$( '.custom-sep' ).on( 'keyup', function() {
					const input = $( this )

					input
						.closest( 'li' )
						.find( 'input.cmb2-option' )
						.val( input.text() )
						.trigger( 'change' )
				} )
			},

			preview() {
				$( '[data-preview]' )
					.on( 'change', function() {
						const $this = $( this )
						let holder = null,
							title = ''

						if ( $this.is( ':radio' ) ) {
							holder = $this.closest( '.cmb-td' )
						}

						if ( null === holder ) {
							return
						}

						if ( holder.hasClass( 'done' ) ) {
							if ( $this.is( ':checked' ) ) {
								title = holder.find( 'h5' )
								title.text(
									title.data( 'title' ).format( $this.val() )
								)
							}
							return
						}

						holder.addClass( 'done' )
						if ( 'title' === $this.data( 'preview' ) ) {
							let out = ''

							out +=
								'<div class="rank-math-preview-title" data-title="Preview"><div>'
							out +=
								'<h5 data-title="' +
								rankMath.postTitle +
								' {0} ' +
								rankMath.blogName +
								'"></h5>'
							out += '<span>' + rankMath.postUri + '</span>'
							out += '</div></div>'

							holder.append( out )

							title = holder.find( 'h5' )
							title.text(
								title.data( 'title' ).format( $this.val() )
							)
						}
					} )
					.trigger( 'change' )
			},

			siteMap() {
				const nginxNotice = $( '.sitemap-nginx-notice' )
				if ( ! nginxNotice.length ) {
					return
				}

				nginxNotice.on( 'click', 'a span', function( e ) {
					e.preventDefault()
					nginxNotice.toggleClass( 'active' )
					return false
				} )
			},

			robotsEvents() {
				$( '.rank-math-robots-data' ).each( function() {
					const isIndex = $( this ).find( 'ul li:first-child input' )
					const isNoIndex = $( this ).find(
						'ul li:nth-child(2) input'
					)

					isIndex.on( 'change', () => {
						if ( isIndex.is( ':checked' ) ) {
							isNoIndex
								.prop( 'checked', false )
								.trigger( 'change' )
						}
					} )

					// Robots NoIndex
					isNoIndex.on( 'change', () => {
						if ( isNoIndex.is( ':checked' ) ) {
							isIndex.prop( 'checked', false )
						}
					} )
				} )
			},
		}

		window.rankMathOptions.init()
	} )
} )( jQuery )
