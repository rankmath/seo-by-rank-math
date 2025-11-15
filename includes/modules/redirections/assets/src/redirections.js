/**
 * External Dependencies
 */
import jQuery from 'jquery'

/**
 * WordPress Dependencies
 */
import { createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Form from './Form'
import { getStore } from '@rank-math-settings/redux/store'

/*!
 * Rank Math - Redirections
 *
 * @version 0.9.0
 * @author  Rank Math
 */

const redirectionsPage = `${ rankMath.adminurl }?page=rank-math-redirections`;

( function( $ ) {
	'use strict'
	// Document Ready
	$( function() {
		getStore()
		const rankMathRedirections = {
			init() {
				this.wrap = $( '.rank-math-redirections-wrap' )
				this.defaultRedirection = {
					header_code: '301',
					status: 'active',
					url_to: '',
					sources: [
						{
							comparison: 'exact',
							pattern: '',
						},
					],
				}
				this.addForm()
				this.addNew()
				this.edit()
				this.importExport()
				this.showMore()
				this.columnActions()
				this.explodePastedContent()
			},

			/**
			 * Add Redirection Form.
			 */
			addForm() {
				createRoot(
					document.getElementById( 'rank-math-redirections-form' )
				).render(
					<Form defaultRedirection={ this.defaultRedirection } />
				)
			},

			/**
			 * Open and handle the form that creates new redirections.
			 */
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

						self.wrap.find( '.rank-math-importexport-form' ).hide()

						if ( form.is( ':visible' ) ) {
							form.hide()
							window.history.pushState( null, {}, redirectionsPage )
							return
						}

						wp.data.dispatch( 'rank-math-settings' ).updateData( { ...self.defaultRedirection } )
						// Show form.
						form.show()
						window.history.pushState( null, {}, event.currentTarget.href )

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

				this.wrap.on(
					'click',
					'.rank-math-button.is-destructive',
					function( event ) {
						event.preventDefault()

						const $this = $( this )
						$this.closest( '.rank-math-editcreate-form' ).hide()

						window.history.pushState( null, {}, redirectionsPage )
					}
				)
			},

			/**
			 * Edit Existing Redirection Rule.
			 */
			edit() {
				const self = this,
					page = $( 'html, body' )

				this.wrap.on(
					'click',
					'.value-url_from',
					( event ) => {
						event.preventDefault()

						const $target = $( event.currentTarget )

						// Check if the clicked element is inside .rank-math-more
						if ( $target.closest( '.rank-math-more' ).length ) {
							$target.closest( '.rank-math-more' ).parent().find( '.rank-math-redirection-edit' ).trigger( 'click' )
						} else {
							$target.parent().find( '.rank-math-redirection-edit' ).trigger( 'click' )
						}

						return false
					}
				)

				this.wrap.on(
					'click',
					'.rank-math-redirection-edit',
					function( event ) {
						event.preventDefault()
						const form = self.wrap.find(
							'.rank-math-editcreate-form'
						)

						self.wrap.find( '.rank-math-importexport-form' ).hide()

						const data = jQuery( this ).data( 'redirection' )
						wp.data.dispatch( 'rank-math-settings' ).updateData( { ...data } )

						// Show form.
						form.show()
						window.history.pushState( null, {}, event.currentTarget.href )

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
			},

			/**
			 * Set up import/export panel.
			 */
			importExport() {
				const self = this

				this.wrap.on(
					'click',
					'.rank-math-redirections-import_export',
					function( event ) {
						event.preventDefault()
						const form = self.wrap.find(
							'.rank-math-importexport-form'
						)

						if ( form.is( ':visible' ) ) {
							form.hide()
							window.history.pushState( null, {}, redirectionsPage )
							return
						}

						self.wrap.find( '.rank-math-editcreate-form' ).hide()

						form.slideToggle( 200 )

						window.history.pushState( null, {}, event.currentTarget.href )
					}
				)
			},

			/**
			 * Add multiple redirection sources when pasting mult-line content in the source fields.
			 */
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
					let input = $( this )
					const addButton = $( '.cmb-add-group-row', group ),
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

			/**
			 * Show more redirection sources.
			 */
			showMore() {
				this.wrap.on( 'click', '.rank-math-showmore', function( event ) {
					event.preventDefault()

					const $this = $( this )
					$this.hide()
					$this.next( '.rank-math-more' ).slideDown()
				} )

				this.wrap.on( 'click', '.rank-math-hidemore', function( event ) {
					event.preventDefault()

					const $this = $( this ).parent()
					$this.hide()
					$this.prev( '.rank-math-showmore' ).show()
				} )
			},

			/**
			 * Column action handlers.
			 */
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
									} else if ( 'deactivate' === action ) {
										self.filterCountAdd( 'inactive' )
										self.filterCountSubstract( 'active' )
									} else if ( 'trash' === action ) {
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
									} else if ( 'delete' === action ) {
										self.filterCountSubstract( 'trashed' )
									} else if ( 'restore' === action ) {
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

			/**
			 * Add one to the post filter count.
			 *
			 * @param {string} filter Filter class.
			 */
			filterCountAdd( filter ) {
				this.filterCount( filter, 'add' )
			},

			/**
			 * Substract one from the post filter count.
			 *
			 * @param {string} filter Filter class.
			 */
			filterCountSubstract( filter ) {
				this.filterCount( filter, 'sub' )
			},

			/**
			 * Add to, or substract one from the post (redirection) filter count.
			 *
			 * @param {string} filter Filter class.
			 * @param {string} action Action, 'add' or 'sub'.
			 */
			filterCount( filter, action ) {
				const $elem = this.wrap.find(
					'form > ul.subsubsub > .' + filter + ' .count'
				)
				let count = parseInt( $elem.text().substr( 1 ) )

				count = 'add' === action ? count + 1 : count - 1
				$elem.text( '(' + count + ')' )
			},
		}

		rankMathRedirections.init()
	} )
}( jQuery ) )
