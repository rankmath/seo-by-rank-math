/*!
 * Rank Math - Redirections
 *
 * @version 0.9.0
 * @author  Rank Math
 */
'use strict'
;( function( $ ) {
	// Document Ready
	$( function() {
		const rankMathRedirections = {
			init() {
				this.wrap = $( '.rank-math-redirections-wrap' )

				this.addNew()
				this.importExport()
				this.showMore()
				this.columnActions()
				this.validateForm()
				this.separateRedirectionTypes()
				this.explodePastedContent()
			},

			addNew() {
				const self = this,
					page = $( 'html, body' )

				this.wrap.on(
					'click',
					'.rank-math-add-new-redirection',
					function( event ) {
						event.preventDefault()
						const form = self.wrap.find(
							'.rank-math-editcreate-form'
						)

						self.wrap.find('.rank-math-importexport-form').hide()

						if ( form.is( ':visible' ) ) {
							form.hide()
							return
						}

						// Reset data.
						form.find(
							'#sources_repeat > .cmb-repeatable-grouping:not(:eq(0))'
						).remove()
						form.find( '> form' )
							.get( 0 )
							.reset()
						form.show()

						page.on(
							'scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove',
							function() {
								page.stop()
							}
						)

						page.animate(
							{ scrollTop: form.position().top },
							'slow',
							function() {
								page.off(
									'scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove'
								)
							}
						)
					}
				)

				this.wrap.on( 'click', '.button-link-delete', function(
					event
				) {
					event.preventDefault()

					const $this = $( this )
					$this.closest( '.rank-math-editcreate-form' ).hide()
				} )
			},

			importExport() {
				const self = this,
					page = $( 'html, body' )

				this.wrap.on(
					'click',
					'.rank-math-redirections-import_export',
					function( event ) {
						event.preventDefault()
						const form = self.wrap.find(
							'.rank-math-importexport-form'
						)

						self.wrap.find('.rank-math-editcreate-form').hide()

						form.slideToggle( 200 )
					}
				)
			},

			validateForm() {
				const buttonPrimary = $(
					'.rank-math-editcreate-form .button-primary'
				)
				$( '.rank-math-editcreate-form > .cmb-form' ).on(
					'submit',
					function( event ) {
						let form = $( this ),
							errorElems = form.find( '.validation-message' ),
							hasError = false

						buttonPrimary.prop( 'disabled', true )

						// Clear error.
						form.find( '.invalid' ).removeClass( 'invalid' )
						errorElems.each( function() {
							$( this )
								.prev( 'br' )
								.remove()
							$( this ).remove()
						} )

						// Handle error.
						form.find( 'input[type="text"]:not(.exclude)' ).each(
							function() {
								const input = $( this )
								if ( ! input.val() || ! input.val().trim() ) {
									hasError = true
									input
										.addClass( 'invalid' )
										.after(
											$(
												'<br><span class="validation-message">' +
													rankMath.emptyError +
													'</span>'
											)
										)
								}
							}
						)

						if ( hasError ) {
							event.preventDefault()
							buttonPrimary.prop( 'disabled', false )
						}
					}
				)
			},

			separateRedirectionTypes() {
				const row = this.wrap.find( '.cmb2-id-header-code' )

				if ( ! row.length ) {
					return
				}

				const clonedRow = row.clone()
				clonedRow
					.find( '.cmb-th label' )
					.text( rankMath.maintenanceMode )
				clonedRow.find( '.cmb2-radio-list li:lt(3)' ).remove()

				row.after( clonedRow )
				row.addClass( 'nob nopb' )
				row.find( '.cmb2-radio-list li:gt(2)' ).remove()

				const group = $( '.cmb2-id-url-to' ),
					field = $( '#url_to' )

				$( '[name=header_code]' ).on( 'change', function() {
					const value = parseInt( $( this ).val() )
					if ( 410 === value || 451 === value ) {
						field.addClass( 'exclude' )
						group.addClass( 'hidden' )
					} else {
						field.removeClass( 'exclude' )
						group.removeClass( 'hidden' )
					}
				} )
				$( '[name=header_code]:checked' ).trigger( 'change' )
			},

			explodePastedContent() {
				const group = $( '#sources_repeat' )

				group.on( 'paste', 'input', function( event ) {
					const pastedData = event.originalEvent.clipboardData.getData(
						'text'
					)

					// Process only if it contains line break.
					const match = /\r|\n/.exec( pastedData )
					if ( ! match ) {
						return true
					}

					// Split by line break & remove empty elements.
					let input = $( this ),
						addButton = $( '.cmb-add-group-row', group ),
						items = pastedData.split( /\r?\n/ ).filter( String ),
						comparisonValue = input
							.closest( '.cmb-field-list' )
							.find( 'select' )
							.val()

					// Now add them as new items
					$.each( items, function( index, item ) {
						input.val( item )
						input
							.closest( '.cmb-field-list' )
							.find( 'select' )
							.val( comparisonValue )
						if ( index < items.length - 1 ) {
							// Number of items to process.
							if (
								rankMath.redirectionPastedContent - 1 <=
								index
							) {
								return false
							}

							addButton.click()
							input = $( '.cmb-repeatable-grouping', group )
								.last()
								.find( 'input' )
						} else {
							input.focus()
						}
					} )
					return false
				} )
			},

			showMore() {
				this.wrap.on( 'click', '.rank-math-showmore', function(
					event
				) {
					event.preventDefault()

					const $this = $( this )
					$this.hide()
					$this.next( '.rank-math-more' ).slideDown()
				} )

				this.wrap.on( 'click', '.rank-math-hidemore', function(
					event
				) {
					event.preventDefault()

					const $this = $( this ).parent()
					$this.hide()
					$this.prev( '.rank-math-showmore' ).show()
				} )
			},

			columnActions() {
				const self = this

				this.wrap.on(
					'click',
					'.rank-math-redirection-action',
					function( event ) {
						event.preventDefault()

						const button = $( this ),
							action = button.data( 'action' ),
							url =
								this.href
									.replace( 'admin.php', 'admin-ajax.php' )
									.replace(
										'page=rank-math-redirections&',
										''
									) +
								'&action=rank_math_redirection_' +
								action

						$.ajax( {
							url,
							type: 'GET',
							success( results ) {
								if ( results && results.success ) {
									if (
										[
											'delete',
											'trash',
											'restore',
										].includes( action )
									) {
										button
											.closest( 'tr' )
											.fadeOut( 800, function() {
												$( this ).remove()
											} )
									} else {
										button
											.closest( 'tr' )
											.toggleClass(
												'rank-math-redirection-activated rank-math-redirection-deactivated'
											)
									}

									if ( 'activate' === action ) {
										self.filterCountAdd( 'active' )
										self.filterCountSubstract( 'inactive' )
									} else if ( 'deactivate' == action ) {
										self.filterCountAdd( 'inactive' )
										self.filterCountSubstract( 'active' )
									} else if ( 'trash' == action ) {
										self.filterCountAdd( 'trashed' )
										self.filterCountSubstract( 'all' )
										if (
											button.closest(
												'.rank-math-redirection-deactivated'
											).length
										) {
											self.filterCountSubstract(
												'inactive'
											)
										} else {
											self.filterCountSubstract(
												'active'
											)
										}
									} else if ( 'delete' == action ) {
										self.filterCountSubstract( 'trashed' )
									} else if ( 'restore' == action ) {
										self.filterCountAdd( 'active' )
										self.filterCountAdd( 'all' )
										self.filterCountSubstract( 'trashed' )
									}
								}
							},
						} )
					}
				)
			},

			filterCountAdd( filter ) {
				this.filterCount( filter, 'add' )
			},

			filterCountSubstract( filter ) {
				this.filterCount( filter, 'sub' )
			},

			filterCount( filter, action ) {
				let $elem = this.wrap.find(
						'form > ul.subsubsub > .' + filter + ' .count'
					),
					count = parseInt( $elem.text().substr( 1 ) )

				count = 'add' === action ? count + 1 : count - 1
				$elem.text( '(' + count + ')' )
			},
		}

		rankMathRedirections.init()
	} )
} )( jQuery )
