/*!
* Rank Math - wpLink
*
* @version 0.9.0
* @author  RankMath
*/

import jQuery from 'jquery'
/* global wpLink */
/*eslint object-shorthand: 0*/
( function( $, wpLinkL10n, wp ) {
	let editor, searchTimer, correctedURL, Query, River
	const emailRegexp = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,63}$/i,
		urlRegexp = /^(https?|ftp):\/\/[A-Z0-9.-]+\.[A-Z]{2,63}[^ "]*$/i,
		inputs = {},
		rivers = {},
		isTouch = ( 'ontouchend' in document )

	function getLink() {
		if ( ! editor ) {
			return null
		}

		return editor.$( 'a[data-wplink-edit="true"]' )
	}

	window.wpLink = {
		timeToTriggerRiver: 150,
		minRiverAJAXDuration: 200,
		riverBottomThreshold: 5,
		keySensitivity: 100,
		lastSearch: '',
		textarea: '',
		modalOpen: false,

		init: function() {
			inputs.wrap = $( '#wp-link-wrap' )
			inputs.dialog = $( '#wp-link' )
			inputs.backdrop = $( '#wp-link-backdrop' )
			inputs.submit = $( '#wp-link-submit' )
			inputs.close = $( '#wp-link-close' )

			// Add Custom Fields
			const relCheckbox = $( '<div class="link-nofollow"><label><span> </span> <input type="checkbox" id="wp-link-nofollow"> ' + wpLinkL10n.relCheckbox + '</label></div><div class="link-sponsored"><label><span> </span> <input type="checkbox" id="wp-link-sponsored"> ' + wpLinkL10n.sponsoredCheckbox + '</label></div>' ),
				linkTitle = $( '<div class="wp-link-title-field"> <label><span>' + wpLinkL10n.linkTitle + '</span> <input id="wp-link-title" type="text"></label></div>' )

			relCheckbox.insertAfter( '#wp-link .link-target' )
			linkTitle.insertAfter( '#wp-link .wp-link-text-field' )

			$( '#wp-link .query-results' ).css( 'top', '290px' )

			// Input
			inputs.text = $( '#wp-link-text' )
			inputs.url = $( '#wp-link-url' )
			inputs.nonce = $( '#_ajax_linking_nonce' )
			inputs.openInNewTab = $( '#wp-link-target' )
			inputs.search = $( '#wp-link-search' )
			inputs.nofollow = $( '#wp-link-nofollow' )
			inputs.sponsored = $( '#wp-link-sponsored' )
			inputs.title = $( '#wp-link-title' )

			// Build Rivers
			rivers.search = new River( $( '#search-results' ) )
			rivers.recent = new River( $( '#most-recent-results' ) )
			rivers.elements = inputs.dialog.find( '.query-results' )

			// Get search notice text
			inputs.queryNotice = $( '#query-notice-message' )
			inputs.queryNoticeTextDefault = inputs.queryNotice.find( '.query-notice-default' )
			inputs.queryNoticeTextHint = inputs.queryNotice.find( '.query-notice-hint' )

			// Bind event handlers
			inputs.dialog.on( 'keydown', ( e ) => wpLink.keydown( e ) )
			inputs.dialog.on( 'keyup', ( e ) => wpLink.keyup( e ) )
			inputs.submit.on( 'click', function( event ) {
				event.preventDefault()
				wpLink.update()
			} )

			inputs.close.add( inputs.backdrop ).add( '#wp-link-cancel button' ).on( 'click', function( event ) {
				event.preventDefault()
				wpLink.close()
			} )

			rivers.elements.on( 'river-select', wpLink.updateFields )

			// Display 'hint' message when search field or 'query-results' box are focused
			inputs.search.on( 'focus.wplink', function() {
				inputs.queryNoticeTextDefault.hide()
				inputs.queryNoticeTextHint.removeClass( 'screen-reader-text' ).show()
			} ).on( 'blur.wplink', function() {
				inputs.queryNoticeTextDefault.show()
				inputs.queryNoticeTextHint.addClass( 'screen-reader-text' ).hide()
			} )

			inputs.search.on( 'keyup input', function() {
				window.clearTimeout( searchTimer )
				searchTimer = window.setTimeout( function() {
					wpLink.searchInternalLinks()
				}, 500 )
			} )

			inputs.url.on( 'paste', function() {
				setTimeout( wpLink.correctURL, 0 )
			} )

			inputs.url.on( 'blur', wpLink.correctURL )
		},

		// If URL wasn't corrected last time and doesn't start with http:, https:, ? # or /, prepend http://
		correctURL: function() {
			const url = $.trim( inputs.url.val() )

			if ( url && correctedURL !== url && ! /^(?:[a-z]+:|#|\?|\.|\/)/.test( url ) ) {
				inputs.url.val( 'http://' + url )
				correctedURL = url
			}
		},

		open: function( editorId, url, text ) {
			if ( 'acf-link-textarea' === editorId ) {
				$( '.wp-link-title-field' ).hide()
			} else {
				$( '.wp-link-title-field' ).show()
			}

			let ed
			const $body = $( document.body )

			$body.addClass( 'modal-open' )
			wpLink.modalOpen = true

			wpLink.range = null

			if ( editorId ) {
				window.wpActiveEditor = editorId
			}

			if ( ! window.wpActiveEditor ) {
				return
			}

			this.textarea = $( '#' + window.wpActiveEditor ).get( 0 )

			if ( 'undefined' !== typeof window.tinymce ) {
				// Make sure the link wrapper is the last element in the body,
				// or the inline editor toolbar may show above the backdrop.
				$body.append( inputs.backdrop, inputs.wrap )

				ed = window.tinymce.get( window.wpActiveEditor )

				if ( ed && ! ed.isHidden() ) {
					editor = ed
				} else {
					editor = null
				}
			}

			if ( ! wpLink.isMCE() && document.selection ) {
				this.textarea.focus()
				this.range = document.selection.createRange()
			}

			inputs.wrap.show()
			inputs.backdrop.show()

			wpLink.refresh( url, text )

			$( document ).trigger( 'wplink-open', inputs.wrap )
		},

		isMCE: function() {
			return editor && ! editor.isHidden()
		},

		refresh: function( url, text ) {
			let linkText = ''

			// Refresh rivers (clear links, check visibility)
			rivers.search.refresh()
			rivers.recent.refresh()

			if ( wpLink.isMCE() ) {
				wpLink.mceRefresh( url, text )
			} else {
				// For the Text editor the "Link text" field is always shown
				if ( ! inputs.wrap.hasClass( 'has-text-field' ) ) {
					inputs.wrap.addClass( 'has-text-field' )
				}

				// Old IE
				if ( document.selection ) {
					linkText = document.selection.createRange().text || text || ''
				} else if (
					'undefined' !== typeof this.textarea.selectionStart &&
					( this.textarea.selectionStart !== this.textarea.selectionEnd )
				) {
					// W3C
					text = this.textarea.value.substring( this.textarea.selectionStart, this.textarea.selectionEnd ) || text || ''
				}

				inputs.text.val( text )
				wpLink.setDefaultValues()
			}

			if ( isTouch ) {
				// Close the onscreen keyboard
				inputs.url.focus().blur()
			} else {
				// Focus the URL field and highlight its contents.
				// If this is moved above the selection changes,
				// IE will show a flashing cursor over the dialog.
				window.setTimeout( function() {
					inputs.url[ 0 ].select()
					inputs.url.focus()
				} )
			}

			// Load the most recent results if this is the first time opening the panel.
			if ( ! rivers.recent.ul.children().length ) {
				rivers.recent.ajax()
			}

			correctedURL = inputs.url.val().replace( /^http:\/\//, '' )
		},

		hasSelectedText: function( linkNode ) {
			let node, nodes, i
			const html = editor.selection.getContent()

			// Partial html and not a fully selected anchor element
			if ( /</.test( html ) && ( ! /^<a [^>]+>[^<]+<\/a>$/.test( html.trim() ) || -1 === html.indexOf( 'href=' ) ) ) {
				return false
			}

			if ( linkNode.length ) {
				nodes = linkNode[ 0 ].childNodes
				if ( ! nodes || ! nodes.length ) {
					return false
				}

				for ( i = nodes.length - 1; 0 <= i; i-- ) {
					node = nodes[ i ]

					if ( 3 !== node.nodeType && ! window.tinymce.dom.BookmarkManager.isBookmarkNode( node ) ) {
						return false
					}
				}
			}

			return true
		},

		mceRefresh: function( searchStr, text ) {
			let linkText, href
			const linkNode = getLink(),
				onlyText = this.hasSelectedText( linkNode )

			if ( linkNode.length ) {
				linkText = linkNode.text()
				href = linkNode.attr( 'href' )

				if ( ! $.trim( linkText ) ) {
					linkText = text || ''
				}

				if ( searchStr && ( urlRegexp.test( searchStr ) || emailRegexp.test( searchStr ) ) ) {
					href = searchStr
				}

				if ( '_wp_link_placeholder' !== href ) {
					inputs.url.val( href )
					inputs.openInNewTab.prop( 'checked', '_blank' === editor.dom.getAttrib( linkNode, 'target' ) )
					inputs.nofollow.prop( 'checked', editor.dom.getAttrib( linkNode, 'rel' ).includes( 'nofollow' ) )
					inputs.sponsored.prop( 'checked', editor.dom.getAttrib( linkNode, 'rel' ).includes( 'sponsored' ) )
					inputs.title.val( editor.dom.getAttrib( linkNode, 'title' ) )
					inputs.submit.val( wpLinkL10n.update )
				} else {
					this.setDefaultValues( linkText )
				}

				if ( searchStr && searchStr !== href ) {
					// The user has typed something in the inline dialog. Trigger a search with it.
					inputs.search.val( searchStr )
				} else {
					inputs.search.val( '' )
				}

				// Always reset the search
				window.setTimeout( function() {
					wpLink.searchInternalLinks()
				} )
			} else {
				linkText = editor.selection.getContent( { format: 'text' } ) || text || ''
				this.setDefaultValues( linkText )
			}

			if ( onlyText ) {
				inputs.text.val( linkText )
				inputs.wrap.addClass( 'has-text-field' )
			} else {
				inputs.text.val( '' )
				inputs.wrap.removeClass( 'has-text-field' )
			}
		},

		close: function( reset ) {
			$( document.body ).removeClass( 'modal-open' )
			wpLink.modalOpen = false

			if ( 'noReset' !== reset ) {
				if ( ! wpLink.isMCE() ) {
					wpLink.textarea.focus()

					if ( wpLink.range ) {
						wpLink.range.moveToBookmark( wpLink.range.getBookmark() )
						wpLink.range.select()
					}
				} else {
					if ( editor.plugins.wplink ) {
						editor.plugins.wplink.close()
					}

					editor.focus()
				}
			}

			inputs.backdrop.hide()
			inputs.wrap.hide()

			correctedURL = false

			$( document ).trigger( 'wplink-close', inputs.wrap )
		},

		getAttrs: function() {
			wpLink.correctURL()
			let rel = inputs.nofollow.prop( 'checked' ) ? 'nofollow' : ''
			if ( inputs.sponsored.prop( 'checked' ) ) {
				rel = rel ? rel + ' sponsored' : 'sponsored'
			}

			const attrs = {
				href: $.trim( inputs.url.val() ),
				target: inputs.openInNewTab.prop( 'checked' ) ? '_blank' : null,
				rel,
			}

			if ( $.trim( inputs.title.val() ) ) {
				attrs.title = $.trim( inputs.title.val() )
			}

			return attrs
		},

		buildHtml: function( attrs ) {
			let html = '<a href="' + attrs.href + '"'

			if ( attrs.target ) {
				html += ' target="' + attrs.target + '"'
			}

			if ( attrs.rel ) {
				html += ' rel="' + attrs.rel + '"'
			}

			if ( attrs.title ) {
				html += ' title="' + attrs.title + '"'
			}

			return html + '>'
		},

		update: function() {
			if ( wpLink.isMCE() ) {
				wpLink.mceUpdate()
			} else {
				wpLink.htmlUpdate()
			}
		},

		htmlUpdate: function() {
			const textarea = wpLink.textarea

			if ( ! textarea ) {
				return
			}

			let html, begin, end, cursor, selection
			const attrs = wpLink.getAttrs()

			const parser = document.createElement( 'a' )
			parser.href = attrs.href

			if ( 'javascript:' === parser.protocol || 'data:' === parser.protocol ) { // jshint ignore:line
				attrs.href = ''
			}

			// If there's no href, return.
			if ( ! attrs.href ) {
				return
			}

			html = wpLink.buildHtml( attrs )

			const text = inputs.text.val()
			// Insert HTML
			if ( document.selection && wpLink.range ) {
				// IE
				// Note: If no text is selected, IE will not place the cursor
				//       inside the closing tag.
				textarea.focus()
				wpLink.range.text = html + ( text || wpLink.range.text ) + '</a>'
				wpLink.range.moveToBookmark( wpLink.range.getBookmark() )
				wpLink.range.select()

				wpLink.range = null
			} else if ( 'undefined' !== typeof textarea.selectionStart ) {
				// W3C
				begin = textarea.selectionStart
				end = textarea.selectionEnd
				selection = text || textarea.value.substring( begin, end )
				html = html + selection + '</a>'
				cursor = begin + html.length

				// If no text is selected, place the cursor inside the closing tag.
				if ( begin === end && ! selection ) {
					cursor -= 4
				}

				textarea.value = (
					textarea.value.substring( 0, begin ) +
					html +
					textarea.value.substring( end, textarea.value.length )
				)

				// Update cursor position
				textarea.selectionStart = textarea.selectionEnd = cursor
			}

			wpLink.close()
			textarea.focus()
			$( textarea ).trigger( 'change' )

			// Audible confirmation message when a link has been inserted in the Editor.
			wp.a11y.speak( wpLinkL10n.linkInserted )
		},

		mceUpdate: function() {
			const attrs = wpLink.getAttrs()
			let $link, text, hasText, $mceCaret

			const parser = document.createElement( 'a' )
			parser.href = attrs.href

			if ( 'javascript:' === parser.protocol || 'data:' === parser.protocol ) { // jshint ignore:line
				attrs.href = ''
			}

			if ( ! attrs.href ) {
				editor.execCommand( 'unlink' )
				wpLink.close()
				return
			}

			$link = editor.$( getLink() )

			editor.undoManager.transact( function() {
				if ( ! $link.length ) {
					editor.execCommand( 'mceInsertLink', false, { href: '_wp_link_placeholder', 'data-wp-temp-link': 1 } )
					$link = editor.$( 'a[data-wp-temp-link="1"]' ).removeAttr( 'data-wp-temp-link' )
					hasText = $.trim( $link.text() )
				}

				if ( ! $link.length ) {
					editor.execCommand( 'unlink' )
				} else {
					if ( inputs.wrap.hasClass( 'has-text-field' ) ) {
						text = inputs.text.val()

						if ( text ) {
							$link.text( text )
						} else if ( ! hasText ) {
							$link.text( attrs.href )
						}
					}

					attrs[ 'data-wplink-edit' ] = null
					attrs[ 'data-mce-href' ] = null // attrs.href
					if ( attrs.hasOwnProperty( 'rel' ) && ! attrs.rel ) {
						attrs.rel = null
					}
					$link.attr( attrs )
				}
			} )

			wpLink.close( 'noReset' )
			editor.focus()

			if ( $link.length ) {
				$mceCaret = $link.parent( '#_mce_caret' )

				if ( $mceCaret.length ) {
					$mceCaret.before( $link.removeAttr( 'data-mce-bogus' ) )
				}

				editor.selection.select( $link[ 0 ] )
				editor.selection.collapse()

				if ( editor.plugins.wplink ) {
					editor.plugins.wplink.checkLink( $link[ 0 ] )
				}
			}

			editor.nodeChanged()
			inputs.title.val( '' )
			// Audible confirmation message when a link has been inserted in the Editor.
			wp.a11y.speak( wpLinkL10n.linkInserted )
		},

		updateFields: function( e, li ) {
			inputs.url.val( li.children( '.item-permalink' ).val() )
			if ( inputs.wrap.hasClass( 'has-text-field' ) && ! inputs.text.val() ) {
				inputs.text.val( li.children( '.item-title' ).text() )
			}
		},

		getUrlFromSelection: function( selection ) {
			if ( ! selection ) {
				if ( this.isMCE() ) {
					selection = editor.selection.getContent( { format: 'text' } )
				} else if ( document.selection && wpLink.range ) {
					selection = wpLink.range.text
				} else if ( 'undefined' !== typeof this.textarea.selectionStart ) {
					selection = this.textarea.value.substring( this.textarea.selectionStart, this.textarea.selectionEnd )
				}
			}

			selection = $.trim( selection )

			if ( selection && emailRegexp.test( selection ) ) {
				// Selection is email address
				return 'mailto:' + selection
			} else if ( selection && urlRegexp.test( selection ) ) {
				// Selection is URL
				return selection.replace( /&amp;|&#0?38;/gi, '&' )
			}

			return ''
		},

		setDefaultValues: function( selection ) {
			inputs.url.val( this.getUrlFromSelection( selection ) )

			// Empty the search field and swap the "rivers".
			inputs.search.val( '' )
			wpLink.searchInternalLinks()

			// Update save prompt.
			inputs.submit.val( wpLinkL10n.save )
		},

		searchInternalLinks: function() {
			let waiting
			const search = inputs.search.val() || ''

			if ( 2 < search.length ) {
				rivers.recent.hide()
				rivers.search.show()

				// Don't search if the keypress didn't change the title.
				if ( wpLink.lastSearch === search ) {
					return
				}

				wpLink.lastSearch = search
				waiting = inputs.search.parent().find( '.spinner' ).addClass( 'is-active' )

				rivers.search.change( search )
				rivers.search.ajax( function() {
					waiting.removeClass( 'is-active' )
				} )
			} else {
				rivers.search.hide()
				rivers.recent.show()
			}
		},

		next: function() {
			rivers.search.next()
			rivers.recent.next()
		},

		prev: function() {
			rivers.search.prev()
			rivers.recent.prev()
		},

		keydown: function( event ) {
			let id

			// Escape key.
			if ( 27 === event.keyCode ) {
				wpLink.close()
				event.stopImmediatePropagation()

			// Tab key.
			} else if ( 9 === event.keyCode ) {
				id = event.target.id

				// wp-link-submit must always be the last focusable element in the dialog.
				// following focusable elements will be skipped on keyboard navigation.
				if ( 'wp-link-submit' === id && ! event.shiftKey ) {
					inputs.close.focus()
					event.preventDefault()
				} else if ( 'wp-link-close' === id && event.shiftKey ) {
					inputs.submit.focus()
					event.preventDefault()
				}
			}

			// Up Arrow and Down Arrow keys.
			if ( event.shiftKey || ( 38 !== event.keyCode && 40 !== event.keyCode ) ) {
				return
			}

			if ( document.activeElement &&
				( 'link-title-field' === document.activeElement.id || 'url-field' === document.activeElement.id ) ) {
				return
			}

			// Up Arrow key.
			const fn = 38 === event.keyCode ? 'prev' : 'next'
			clearInterval( wpLink.keyInterval )
			wpLink[ fn ]()
			wpLink.keyInterval = setInterval( wpLink[ fn ], wpLink.keySensitivity )
			event.preventDefault()
		},

		keyup: function( event ) {
			// Up Arrow and Down Arrow keys.
			if ( 38 === event.keyCode || 40 === event.keyCode ) {
				clearInterval( wpLink.keyInterval )
				event.preventDefault()
			}
		},

		delayedCallback: function( func, delay ) {
			let timeoutTriggered, funcTriggered, funcArgs, funcContext

			if ( ! delay ) {
				return func
			}

			setTimeout( function() {
				if ( funcTriggered ) {
					return func.apply( funcContext, funcArgs )
				}

				// Otherwise, wait.
				timeoutTriggered = true
			}, delay )

			return function() {
				if ( timeoutTriggered ) {
					return func.apply( this, arguments )
				}

				// Otherwise, wait.
				funcArgs = arguments
				funcContext = this
				funcTriggered = true
			}
		},
	}

	River = function( element, search ) {
		const self = this
		this.element = element
		this.ul = element.children( 'ul' )
		this.contentHeight = element.children( '#link-selector-height' )
		this.waiting = element.find( '.river-waiting' )

		this.change( search )
		this.refresh()

		$( '#wp-link .query-results, #wp-link #link-selector' ).on( 'scroll', function() {
			self.maybeLoad()
		} )
		element.on( 'click', 'li', function( event ) {
			self.select( $( this ), event )
		} )
	}

	$.extend( River.prototype, {
		refresh: function() {
			this.deselect()
			this.visible = this.element.is( ':visible' )
		},
		show: function() {
			if ( ! this.visible ) {
				this.deselect()
				this.element.show()
				this.visible = true
			}
		},
		hide: function() {
			this.element.hide()
			this.visible = false
		},

		// Selects a list item and triggers the river-select event.
		select: function( li, event ) {
			if ( li.hasClass( 'unselectable' ) || li === this.selected ) {
				return
			}

			this.deselect()
			this.selected = li.addClass( 'selected' )

			// Make sure the element is visible
			const liHeight = li.outerHeight(),
				elHeight = this.element.height(),
				liTop = li.position().top,
				elTop = this.element.scrollTop()

			// Make first visible element
			if ( 0 > liTop ) {
				this.element.scrollTop( elTop + liTop )
			} else if ( liTop + liHeight > elHeight ) {
				// Make last visible element
				this.element.scrollTop( elTop + liTop - elHeight + liHeight )
			}

			// Trigger the river-select event
			this.element.trigger( 'river-select', [ li, event, this ] )
		},
		deselect: function() {
			if ( this.selected ) {
				this.selected.removeClass( 'selected' )
			}
			this.selected = false
		},
		prev: function() {
			if ( ! this.visible ) {
				return
			}

			let to
			if ( this.selected ) {
				to = this.selected.prev( 'li' )
				if ( to.length ) {
					this.select( to )
				}
			}
		},
		next: function() {
			if ( ! this.visible ) {
				return
			}

			const to = this.selected ? this.selected.next( 'li' ) : $( 'li:not(.unselectable):first', this.element )
			if ( to.length ) {
				this.select( to )
			}
		},
		ajax: function( callback ) {
			const self = this,
				delay = 1 === this.query.page ? 0 : wpLink.minRiverAJAXDuration,
				response = wpLink.delayedCallback( function( results, params ) {
					self.process( results, params )
					if ( callback ) {
						callback( results, params )
					}
				}, delay )

			this.query.ajax( response )
		},
		change: function( search ) {
			if ( this.query && this._search === search ) {
				return
			}

			this._search = search
			this.query = new Query( search )
			this.element.scrollTop( 0 )
		},
		process: function( results, params ) {
			let list = '',
				alt = true,
				classes = ''

			const firstPage = 1 === params.page

			if ( ! results ) {
				if ( firstPage ) {
					list += '<li class="unselectable no-matches-found"><span class="item-title"><em>' +
						wpLinkL10n.noMatchesFound + '</em></span></li>'
				}
			} else {
				$.each( results, function() {
					classes = alt ? 'alternate' : ''
					classes += this.title ? '' : ' no-title'
					list += classes ? '<li class="' + classes + '">' : '<li>'
					list += '<input type="hidden" class="item-permalink" value="' + this.permalink + '" />'
					list += '<span class="item-title">'
					list += this.title ? this.title : wpLinkL10n.noTitle
					list += '</span><span class="item-info">' + this.info + '</span></li>'
					alt = ! alt
				} )
			}

			this.ul[ firstPage ? 'html' : 'append' ]( list )
		},
		maybeLoad: function() {
			const self = this,
				el = this.element,
				bottom = el.scrollTop() + el.height()

			if ( ! this.query.ready() || bottom < this.contentHeight.height() - wpLink.riverBottomThreshold ) {
				return
			}

			setTimeout( function() {
				const newTop = el.scrollTop(),
					newBottom = newTop + el.height()

				if ( ! self.query.ready() || newBottom < self.contentHeight.height() - wpLink.riverBottomThreshold ) {
					return
				}

				self.waiting.addClass( 'is-active' )
				el.scrollTop( newTop + self.waiting.outerHeight() )

				self.ajax( function() {
					self.waiting.removeClass( 'is-active' )
				} )
			}, wpLink.timeToTriggerRiver )
		},
	} )

	Query = function( search ) {
		this.page = 1
		this.allLoaded = false
		this.querying = false
		this.search = search
	}

	$.extend( Query.prototype, {
		ready: function() {
			return ! ( this.querying || this.allLoaded )
		},
		ajax: function( callback ) {
			const self = this,
				query = {
					action: 'wp-link-ajax',
					page: this.page,
					_ajax_linking_nonce: inputs.nonce.val(),
				}

			if ( this.search ) {
				query.search = this.search
			}

			this.querying = true

			$.post( window.ajaxurl, query, function( r ) {
				self.page++
				self.querying = false
				self.allLoaded = ! r
				callback( r, query )
			}, 'json' )
		},
	} )

	$( document ).ready( wpLink.init )
}( jQuery, window.wpLinkL10n, window.wp ) )
