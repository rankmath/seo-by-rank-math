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
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import ajax from '@helpers/ajax'
import addNotice from '@helpers/addNotice'
;( function( $ ) {
	// First, checks if it isn't implemented yet.
	if ( ! String.prototype.format ) {
		String.prototype.format = function() {
			const args = arguments
			return this.replace( /{(\d+)}/g, function( match, number ) {
				return 'undefined' !== typeof args[ number ]
					? args[ number ]
					: match
			} )
		}
	}

	// The enhanced trimRight() function also accepts characters to be trimmed.
	String.prototype.trimRight = function( charlist ) {
		if ( undefined === charlist ) {
			charlist = '\s'
		}

		return this.replace( new RegExp( '[' + charlist + ']+$' ), '' )
	}

	// Document Ready
	$( function() {
		window.rankMathAdmin = {
			init() {
				this.misc()
				this.tabs()
				this.searchConsole()
				this.dependencyManager()
			},

			misc() {
				if ( 'undefined' !== typeof jQuery.fn.select2 ) {
					$( '[data-s2]' ).select2()
				}

				$( '.cmb-group-text-only,.cmb-group-fix-me' ).each( function() {
					const $this = $( this )
					const nested = $this.find( '.cmb-repeatable-group' )
					const th = nested.find( '> .cmb-row:eq(0) > .cmb-th' )

					$this.prepend(
						'<div class="cmb-th"><label>' +
							th.find( 'h2' ).text() +
							'</label></div>'
					)
					nested
						.find( '.cmb-add-row' )
						.append(
							'<span class="cmb2-metabox-description">' +
								th.find( 'p' ).text() +
								'</span>'
						)

					th.parent().remove()
				} )

				$( '.rank-math-collapsible-trigger' ).on( 'click', function(
					event
				) {
					event.preventDefault()

					const trigger = $( this )
					const target = $( '#' + trigger.data( 'target' ) )

					trigger.toggleClass( 'open' )
					target.toggleClass( 'open' )
				} )

				// Review Schema disabled.
				const schemaDropdown = $( '#rank_math_rich_snippet' )
				const hasReview = schemaDropdown.find( 'option[value=review]' )
				const schemaType = schemaDropdown.val()
				if ( hasReview ) {
					hasReview.prop( 'disabled', true )
					if ( 'review' === schemaType ) {
						$(
							'.cmb2-id-rank-math-review-schema-notice'
						).removeClass( 'hidden' )
					}

					schemaDropdown.on( 'change', function() {
						if (
							null !== schemaDropdown.val() &&
							'review' !== schemaDropdown.val()
						) {
							$(
								'.cmb2-id-rank-math-review-schema-notice'
							).addClass( 'hidden' )
						}
					} )
				}
			},

			// Search console
			searchConsole() {
				const consoleWrapper = $(
					'.cmb2-id-console-authorization-code'
				)
				const authorizeButton = $( '.rank-math-get-authorization-code' )
				const consoleCode = $( '#console_authorization_code' )
				const consoleDpInfo = $( '#gsc-dp-info' )
				const consoleProfile = $( '#console_profile' )
				const consoleProfileField = consoleProfile.parent()
				const buttonRefresh = consoleProfileField.find(
					'.rank-math-refresh'
				)
				const consoleCodeField = consoleCode.parent()

				const selector = $( 'body' ).hasClass(
					'rank-math-wizard-body--searchconsole'
				)
					? $( '> p:first-of-type', '.cmb-form' )
					: $( 'h1', '.rank-math-wrap-settings' )

				authorizeButton.on( 'click', function( event ) {
					event.preventDefault()
					consoleWrapper.addClass( 'authorizing' )
					authorizeButton
						.removeClass( 'button-primary' )
						.addClass( 'button-secondary' )
					window.open( this.href, '', 'width=800, height=600' )
				} )

				consoleCodeField.on(
					'click',
					'.rank-math-authorize-account',
					function( event ) {
						event.preventDefault()
						const buttonPrimary = $( this )
						buttonPrimary.prop( 'disabled', true )
						if ( consoleCode.data( 'authorized' ) ) {
							ajax( 'search_console_deauthentication' )
								.always( function() {
									buttonPrimary.prop( 'disabled', false )
								} )
								.done( function() {
									consoleCode.val( '' )
									consoleCode.show()
									consoleCode.data( 'authorized', false )
									consoleCodeField
										.find(
											'.rank-math-get-authorization-code'
										)
										.show()

									consoleWrapper
										.removeClass( 'authorized' )
										.addClass( 'unauthorized' )
									buttonPrimary.html(
										__( 'Authorize', 'rank-math' )
									)
									consoleProfile.prop( 'disabled', true )
									buttonRefresh.prop( 'disabled', true )
								} )
							return false
						}
						consoleCode.addClass( 'input-loading' )

						ajax( 'search_console_authentication', {
							code: consoleCode.val(),
						} )
							.always( function() {
								buttonPrimary.prop( 'disabled', false )
								consoleCode.removeClass( 'input-loading' )
							} )
							.done( function( result ) {
								if ( result && ! result.success ) {
									addNotice( result.error, 'error', selector )
								}

								if ( result && result.success ) {
									consoleCode.hide()
									consoleCode.data( 'authorized', true )
									consoleCodeField
										.find(
											'.rank-math-get-authorization-code'
										)
										.hide()
									buttonPrimary.html( 'De-authorize Account' )
									buttonRefresh.trigger( 'click' )
									consoleProfile.removeAttr( 'disabled' )
									consoleWrapper
										.removeClass(
											'unauthorized authorizing'
										)
										.addClass( 'authorized' )
								}
							} )
					}
				)

				consoleProfile
					.on( 'change', function() {
						if (
							null !== consoleProfile.val() &&
							0 === consoleProfile.val().indexOf( 'sc-domain:' )
						) {
							consoleDpInfo.removeClass( 'hidden' )
						} else {
							consoleDpInfo.addClass( 'hidden' )
						}
					} )
					.change()

				buttonRefresh.on( 'click', function( event ) {
					event.preventDefault()
					buttonRefresh.prop( 'disabled', true )
					consoleProfile.addClass( 'input-loading' )

					ajax( 'search_console_get_profiles' )
						.always( function() {
							buttonRefresh.prop( 'disabled', false )
							$( '.console-cache-update-manually' ).prop(
								'disabled',
								false
							)
							consoleProfile.removeClass( 'input-loading' )
						} )
						.done( function( result ) {
							if ( result && ! result.success ) {
								addNotice( result.error, 'error', selector )
							}
							if ( result && result.success ) {
								const current =
									result.selected || consoleProfile.val()
								consoleProfile.html( '' )
								$.each( result.profiles, function( val, text ) {
									consoleProfile.append(
										'<option value="' +
											val +
											'">' +
											text +
											'</option>'
									)
								} )
								consoleProfile.val(
									current ||
										Object.keys( result.profiles )[ 0 ]
								)
								buttonRefresh.removeClass( 'hidden' )
							}
						} )
				} )
			},

			dependencyManager() {
				const self = this

				// Group correction
				const elem = $( '.cmb-form, .rank-math-metabox-wrap' )
				$( '.cmb-repeat-group-wrap', elem ).each( function() {
					const $this = $( this )
					const dep = $this.next( '.rank-math-cmb-dependency.hidden' )

					if ( dep.length ) {
						$this.find( '> .cmb-td' ).append( dep )
					}
				} )

				$( '.rank-math-cmb-dependency', elem ).each( function() {
					self.loopDependencies( $( this ) )
				} )

				$( 'input, select', elem ).on( 'change', function() {
					const fieldName = $( this ).attr( 'name' )
					$( 'span[data-field="' + fieldName + '"]' ).each(
						function() {
							self.loopDependencies(
								$( this ).closest( '.rank-math-cmb-dependency' )
							)
						}
					)
				} )
			},

			checkDependency( currentValue, desiredValue, comparison ) {
				// Multiple values
				if (
					'string' === typeof desiredValue &&
					desiredValue.includes( ',' ) &&
					'=' === comparison
				) {
					return desiredValue.includes( currentValue )
				}
				if (
					'string' === typeof desiredValue &&
					desiredValue.includes( ',' ) &&
					'!=' === comparison
				) {
					return ! desiredValue.includes( currentValue )
				}
				if ( '=' === comparison && currentValue === desiredValue ) {
					return true
				}
				if ( '==' === comparison && currentValue === desiredValue ) {
					return true
				}
				if ( '>=' === comparison && currentValue >= desiredValue ) {
					return true
				}
				if ( '<=' === comparison && currentValue <= desiredValue ) {
					return true
				}
				if ( '>' === comparison && currentValue > desiredValue ) {
					return true
				}
				if ( '<' === comparison && currentValue < desiredValue ) {
					return true
				}
				if ( '!=' === comparison && currentValue !== desiredValue ) {
					return true
				}

				return false
			},

			loopDependencies( $container ) {
				const self = this
				const relation = $container.data( 'relation' )

				let passed = null

				$container.find( 'span' ).each( function() {
					const $this = $( this )
					const field = $( "[name='" + $this.data( 'field' ) + "']" )
					let fieldValue = field.val()

					if ( field.is( ':radio' ) ) {
						fieldValue = field.filter( ':checked' ).val()
					}

					if ( field.is( ':checkbox' ) ) {
						fieldValue = field.filter( ':checked' ).val()
					}

					const result = self.checkDependency(
						fieldValue,
						$this.data( 'value' ),
						$this.data( 'comparison' )
					)

					if ( 'or' === relation && result ) {
						passed = true
						return false
					} else if ( 'and' === relation ) {
						if ( null === passed ) {
							passed = result
						} else {
							passed = passed && result
						}
					}
				} )

				let hideMe = $container.closest( '.rank-math-cmb-group' )
				if ( ! hideMe.length ) {
					hideMe = $container.closest( '.cmb-row' )
				}

				if ( passed ) {
					hideMe.slideDown( 300 )
				} else {
					hideMe.hide()
				}
			},

			tabs() {
				const tabNavigation = $( '.rank-math-tabs-navigation' )
				if ( ! tabNavigation.length ) {
					return
				}

				tabNavigation.each( function() {
					const wrapper = $( this )
					const container = wrapper.closest( '.rank-math-tabs' )
					const nav = $( '>a', wrapper )
					const panels = $(
						'>.rank-math-tabs-content>.rank-math-tab',
						container
					)
					const activeClass =
						wrapper.data( 'active-class' ) || 'active'

					const moveHeader = wrapper.hasClass( 'before-header' )

					// Click Event
					nav.on( 'click', function() {
						const $this = $( this )
						const target = $( $this.attr( 'href' ) )

						nav.removeClass( activeClass )
						panels.hide()

						//  Move Tab Header before the options panel box.
						if ( moveHeader ) {
							const cloneTitle = target
								.find( '.cmb-type-title.tab-header' )
								.clone()
							cloneTitle.addClass( 'before-header-title' )
							$( '.before-header-title' ).remove()
							container.prepend( cloneTitle )
						}

						$this.addClass( activeClass )
						target.show()

						return false
					} )

					let target =
						window.location.hash ||
						window.localStorage.getItem( container.attr( 'id' ) )
					if ( null === target ) {
						nav.eq( 0 ).trigger( 'click' )
					} else {
						target = $( 'a[href="' + target + '"]', wrapper )
						if ( target.length ) {
							target.trigger( 'click' )
						} else {
							nav.eq( 0 ).trigger( 'click' )
						}
					}

					// Set min height
					tabNavigation
						.next()
						.css( 'min-height', wrapper.outerHeight() )
				} )
			},

			variableInserter( isPreview ) {
				const fields = $(
					'input[type=text], textarea',
					'.rank-math-supports-variables'
				)

				isPreview = undefined === isPreview ? true : isPreview

				// Early bail if no field needed on the screen.
				if ( ! fields.length ) {
					return
				}

				const self = this
				const body = $( 'body' )
				let currentExclude

				// Wrap fields.
				fields.attr( 'autocomplete', 'off' )
				fields.wrap( '<div class="rank-math-variables-wrap"/>' )

				$( '.rank-math-variables-wrap' ).append(
					'<a href="#" class="rank-math-variables-button button button-secondary"><span class="dashicons dashicons-arrow-down-alt2"></span></a>'
				)
				if ( isPreview ) {
					// Add trigger button
					$( '.rank-math-variables-wrap' ).after(
						'<div class="rank-math-variables-preview" data-title="' +
							__( 'Example', 'rank-math' ) +
							'"/>'
					)

					// Trigger Fields
					fields.on( 'rank_math_variable_change input', function(
						event
					) {
						const holder = $( event.currentTarget )
						let value = self.replaceVariables( holder.val() )

						if (
							60 < value.length &&
							0 <= holder.attr( 'name' ).indexOf( 'title' )
						) {
							value = value.substring( 0, 60 ) + '...'
						} else if (
							160 < value.length &&
							0 <= holder.attr( 'name' ).indexOf( 'description' )
						) {
							value = value.substring( 0, 160 ) + '...'
						}

						let htmldecoded = $( '<textarea/>' ).html( value ).val()

						holder
							.parent()
							.next( '.rank-math-variables-preview' )
							.text( htmldecoded )
					} )

					fields.trigger( 'rank_math_variable_change' )
				}

				// Add dropdown
				const list = $( '<ul/>' )
				const dropdown = $(
					'<div class="rank-math-variables-dropdown"><input type="text" placeholder="' +
						__( 'Search &hellip;', 'rank-math' ) +
						'"></div>'
				)
				$.each( rankMath.variables, function() {
					list.append(
						'<li data-var="%' +
							this.variable +
							'%"' +
							this.example +
							'><strong>' +
							this.name +
							'</strong><span>' +
							this.description +
							'</span></li>'
					)
				} )

				// Append list to body
				dropdown.append( list )
				$( '.rank-math-variables-wrap:eq(0)' ).append( dropdown )

				// Hide on body click
				const nots = $(
					'.rank-math-variables-button, .rank-math-variables-button *, .rank-math-variables-dropdown, .rank-math-variables-dropdown *'
				)
				body.on( 'click', function( event ) {
					if ( ! $( event.target ).is( nots ) ) {
						dropdownHide()
					}
				} )

				// Trigger button
				const input = dropdown.find( 'input' )
				const lis = dropdown.find( 'li' )
				body.on( 'click', '.rank-math-variables-button', function(
					event
				) {
					event.preventDefault()

					const dropdownCaret = $( this )

					dropdownCaret.after( dropdown )
					lis.show()

					// Now exclude.
					currentExclude = dropdownCaret
						.prev()
						.data( 'exclude-variables' )
					if ( undefined !== currentExclude ) {
						currentExclude = currentExclude.split( ',' )
						excludeVariable()
					}

					dropdown.show()
					input.val( '' ).focus()
				} )

				// Insert Variable
				dropdown.on( 'click', 'li', function( event ) {
					event.preventDefault()

					const $this = $( this )
					const holder = $this
						.closest( '.rank-math-variables-wrap' )
						.find( 'input,textarea' )

					holder.val(
						$.trim( holder.val() ) + ' ' + $this.data( 'var' )
					)
					holder
						.trigger( 'rank_math_variable_change' )
						.trigger( 'input' )
					dropdownHide()
				} )

				// Search
				dropdown.on( 'keyup', 'input', function( event ) {
					event.preventDefault()

					const query = $( this )
						.val()
						.toLowerCase()

					if ( 2 > query.length ) {
						lis.show()
						excludeVariable()
						return
					}

					lis.hide().each( function() {
						const li = $( this )

						if (
							-1 !==
							li
								.text()
								.toLowerCase()
								.indexOf( query )
						) {
							li.show()
						}
					} )

					excludeVariable()
				} )

				function excludeVariable() {
					if ( undefined === currentExclude ) {
						return
					}

					currentExclude.forEach( ( variable ) => {
						dropdown
							.find( '[data-var="%' + variable + '%"]' )
							.hide()
					} )
				}

				function dropdownHide() {
					currentExclude = undefined
					dropdown.hide()
				}
			},

			replaceVariables( text ) {
				$.each( rankMath.variables, function( tag ) {
					if ( ! this.example ) {
						return true
					}

					// Replace (stuff) with \(.*\)
					const re = new RegExp( '\\([a-z]+\\)', 'g' )
					tag = tag.replace( re, '\\(.*?\\)' )
					text = text.replace(
						new RegExp( '%+' + tag + '%+', 'g' ),
						this.example
					)
				} )

				return text
			},
		}

		window.rankMathAdmin.init()
	} )
}( jQuery ) )
