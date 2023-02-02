/**
 * External Dependencies
 */
import jQuery from 'jquery'

/*!
 * Rank Math - SEO Analyzer
 *
 * @version 0.9.0
 * @author  Rank Math
 */
( function( $ ) {
	'use strict'

	$( function() {
		const RankMathSeoAnalysis = {
			init() {
				this.wrap = $( '.rank-math-seo-analysis-wrap' )
				this.results = this.wrap.find( '.rank-math-results-wrapper' )
				this.progress = this.wrap.find( '.progress' )
				this.counter = this.wrap.find( '.progress-bar span' )

				this.events()
				this.graphs()
				this.single()
				this.filters()
			},

			/**
			 * Set up event handlers.
			 */
			events() {
				const self = this

				/**
				 * Start Analysis Again button.
				 */
				this.wrap.on( 'click', '.rank-math-recheck', function( event ) {
					const recheck_button = $( this )
					event.preventDefault()

					self.wrap.addClass( 'is-loading' ).removeClass( 'is-loaded' )
					self.results.empty()

					const showError = function( notice ) {
						$( '.notice-seo-analysis-error' ).remove()
						if ( notice.length === 0 ) {
							return
						}
						self.wrap.find( '.rank-math-analyzer-result' ).first().prepend( notice )
						notice.slideDown()
					}

					const payload = {
						action: 'rank_math_analyze',
						security: rankMath.security,
						u: self.wrap.find( '.rank-math-analyze-url' ).val(),
					}

					if ( self.wrap.find( 'input[type="hidden"]' ).length > 0 ) {
						self.wrap.find( 'input[type="hidden"]' ).each( function() {
							payload[ $( this ).attr( 'name' ) ] = $( this ).val()
						} )
					}

					$.ajax( {
						url: ajaxurl,
						type: 'POST',
						dataType: 'html',
						data: payload,
						beforeSend() {
							self.renderProgressBar()
						},
						complete() {
							clearInterval( self.interval )
							self.progress.css( 'width', '100%' )
							self.counter.html( '100%' )
						},
						error() {
							const notice = $( '<div class="notice notice-error is-dismissible notice-seo-analysis-error"><p>An error occured.</p></div>' ).hide()
							showError( notice )
							self.wrap.addClass( 'is-loaded' ).removeClass( 'is-loading' )
						},
						success( results ) {
							self.results.html( results )
							const notice = self.results.find( '.notice' )
							if ( $( results ).find( '#rank-math-circle-progress' ).length !== 0 ) {
								self.wrap.addClass( 'is-loaded' ).removeClass( 'is-loading' )
								self.graphs()
							} else {
								self.wrap.removeClass( 'is-loaded is-loading' )
								self.progress.css( 'width', '0%' )
								self.counter.html( '0%' )
							}
							showError( notice )
						},
					} )
				} )

				/**
				 * How to Fix toggle button.
				 */
				self.wrap.on( 'click', '.result-action', function( event ) {
					event.preventDefault()
					$( this ).parent( 'div' ).toggleClass( 'expanded' )
				} )

				/**
				 * Enable Auto Updates button.
				 */
				self.wrap.on( 'click', '.enable-auto-update', function( event ) {
					event.preventDefault()
					$.ajax( {
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'rank_math_enable_auto_update',
							security: rankMath.security,
						},
					} )
					$( this ).closest( '.auto-update-disabled' )
						.addClass( 'hidden' )
						.siblings( '.auto-update-enabled' )
						.removeClass( 'hidden' )
						.closest( '.row-description' )
						.find( '.status-icon' )
						.removeClass( 'status-warning dashicons-warning' )
						.addClass( 'status-ok dashicons-yes' )
				} )
			},

			/**
			 * Analysis Progress Bar.
			 */
			renderProgressBar() {
				let self = this,
					width = 0

				self.progress.css( 'width', width )
				self.interval = setInterval( function() {
					width++

					if ( 100 === width ) {
						clearInterval( self.interval )
					}

					self.counter.html( width + '%' )
					self.progress.css( 'width', width + '%' )
				}, 30 )
			},

			/**
			 * Circular progress bar for total result score.
			 */
			graphs() {
				const circle = $( '#rank-math-circle-progress' )

				if ( 0 > circle.length ) {
					return
				}

				let val = circle.data( 'result' ),
					resultcolors = [ '#10AC84', '#10AC84' ] // Green.

				if ( 0.5 > val ) {
					resultcolors = [ '#ed5e5e', '#ed5e5e' ] // Red.
				} else if ( 0.7 > val ) {
					resultcolors = [ '#FF9F43', '#FF9F43' ] // Yellow.
				}

				circle.circleProgress( {
					value: val,
					size: 207,
					thickness: 15,
					lineCap: 'round',
					emptyFill: '#e9e9ea',
					fill: { gradient: resultcolors },
					startAngle: -Math.PI / 2,
				} )
			},

			/**
			 * Single page analysis event handlers.
			 */
			single() {
				const self = this,
					current = self.wrap.find( '.rank-math-current-url' ),
					url = self.wrap.find( '.rank-math-analyze-url' ),
					recheck = self.wrap.find( '.rank-math-recheck' ),
					change = self.wrap.find( '.rank-math-changeurl' ),
					ok = self.wrap.find( '.rank-math-changeurl-ok' )

				if ( ! url.length ) {
					return self
				}

				change.on( 'click', function() {
					// Hide.
					current.hide()
					change.hide()

					// Show.
					url.show()
					ok.show()

					return false
				} )

				ok.on( 'click', function() {
					// Hide.
					url.hide()
					ok.hide()

					// Show.
					current.show()
					change.show()

					// Change url.
					current.html( url.val() )

					recheck.trigger( 'click' )

					return false
				} )

				/**
				 * Start the analysis when the user hits Enter.
				 */
				url.on( 'keypress', function( event ) {
					if ( 13 === event.keyCode ) {
						ok.trigger( 'click' )
					}
				} )

				// Auto-run single page analysis.
				recheck.not( '.no-autostart' ).trigger( 'click' )
			},

			filters() {
				const self = this,
					filters = self.wrap.find( '.rank-math-result-filter' )

				self.wrap.on( 'click', filters, function( event ) {
					const filter = $( event.target ).data( 'filter' ),
						filters = self.wrap.find( '.rank-math-result-filter' )

					if ( 'undefined' === typeof filter ) {
						return
					}

					event.preventDefault()

					const resultsCategories = self.wrap.find( '.rank-math-result-table' ),
						results = self.wrap.find( '.table-row' )

					filters.removeClass( 'active' )
					$( event.target ).addClass( 'active' )

					resultsCategories.addClass( 'hidden' )
					results.addClass( 'hidden' )

					if ( 'all' === filter ) {
						resultsCategories.removeClass( 'hidden' )
						results.removeClass( 'hidden' )
						return
					}

					resultsCategories.filter( '.rank-math-result-statuses-' + filter ).removeClass( 'hidden' )
					results.filter( '.rank-math-result-status-' + filter ).removeClass( 'hidden' )
				} )
			},
		}

		RankMathSeoAnalysis.init()
	} )
}( jQuery ) )
