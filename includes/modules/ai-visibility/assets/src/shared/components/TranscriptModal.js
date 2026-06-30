/**
 * TranscriptModal — two-panel transcript modal.
 *
 * @since 1.0.273
 */

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { Modal } from '@wordpress/components'

/**
 * Internal dependencies
 */
import Button from './Button'
import './TranscriptModal.scss'

/**
 * Apply syntax highlighting to a JSON string.
 * Escapes HTML entities first, then wraps tokens in <span class="json-*"> elements.
 * Safe to use with dangerouslySetInnerHTML since input is our own serialised data.
 *
 * Colour classes (defined in TranscriptModal.scss):
 *   .json-key     → amber  #F2B02D  (object keys)
 *   .json-string  → teal   #5DBEC4  (string values)
 *   .json-number  → white  #e5e7eb  (numbers)
 *   .json-boolean → teal   #5DBEC4  (true / false)
 *   .json-null    → grey   #9ca3af  (null)
 *
 * @param {string} json JSON string to highlight.
 * @return {string} HTML string with <span> tokens.
 */
const highlightJson = ( json ) => {
	// 1. Escape HTML entities.
	const escaped = json
		.replace( /&/g, '&amp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' )

	// 2. Tokenise and wrap.
	return escaped.replace(
		// Matches: "key":  |  "string value"  |  true/false/null  |  numbers
		/("(?:\\u[a-fA-F0-9]{4}|\\[^u]|[^\\"])*"(?:\s*:)?|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g,
		( token ) => {
			if ( token.endsWith( ':' ) || ( token.startsWith( '"' ) && token.endsWith( '":' ) ) ) {
				return `<span class="json-key">${ token }</span>`
			}
			if ( token.startsWith( '"' ) ) {
				return `<span class="json-string">${ token }</span>`
			}
			if ( token === 'true' || token === 'false' ) {
				return `<span class="json-boolean">${ token }</span>`
			}
			if ( token === 'null' ) {
				return `<span class="json-null">${ token }</span>`
			}
			return `<span class="json-number">${ token }</span>`
		}
	)
}

/**
 * Copy text to clipboard with execCommand fallback.
 *
 * @param {string}   text      Text to copy.
 * @param {Function} onSuccess Called after successful copy.
 */
const copyToClipboard = ( text, onSuccess ) => {
	const done = () => {
		onSuccess()
	}

	if ( window.navigator.clipboard ) {
		window.navigator.clipboard.writeText( text ).then( done ).catch( () => fallback() )
	} else {
		fallback()
	}

	function fallback() {
		const el = document.createElement( 'textarea' )
		el.value = text
		el.style.cssText = 'position:fixed;top:0;left:0;opacity:0;pointer-events:none'
		document.body.appendChild( el )
		el.focus()
		el.select()
		try {
			document.execCommand( 'copy' )
			done()
		} catch {
			// Silently swallow — clipboard unavailable in this context.
		}
		document.body.removeChild( el )
	}
}

/**
 * TranscriptModal component.
 *
 * @param {Object}   props
 * @param {Object}   props.entry   The transcript entry to display.
 * @param {Function} props.onClose Called when the modal should close.
 * @return {JSX.Element} Two-panel transcript modal.
 */
/**
 * Prepare an entry for JSON serialisation.
 * Parses `extraction_data` if it is a JSON string so it renders as a nested
 * object rather than an escaped string.
 *
 * @param {Object} entryObj The transcript entry.
 * @return {Object} Entry with `extraction_data` parsed.
 */
const prepareEntry = ( entryObj ) => {
	if ( typeof entryObj.extraction_data !== 'string' ) {
		return entryObj
	}
	try {
		return { ...entryObj, extraction_data: JSON.parse( entryObj.extraction_data ) }
	} catch {
		return entryObj
	}
}

const TranscriptModal = ( { entry, onClose } ) => {
	const ns = 'rank-math-ai-visibility-transcript-modal'
	const [ copiedText, setCopiedText ] = useState( false )
	const [ copiedJson, setCopiedJson ] = useState( false )

	const handleCopyText = () => {
		const text = [
			__( 'Query:', 'seo-by-rank-math' ) + ' ' + ( entry.query || '' ),
			'',
			__( 'Response:', 'seo-by-rank-math' ) + ' ' + ( entry.response || entry.excerpt || '' ),
		].join( '\n' )
		copyToClipboard( text, () => {
			setCopiedText( true )
			setTimeout( () => setCopiedText( false ), 2000 )
		} )
	}

	const handleCopyJson = () => {
		copyToClipboard( JSON.stringify( prepareEntry( entry ), null, 2 ), () => {
			setCopiedJson( true )
			setTimeout( () => setCopiedJson( false ), 2000 )
		} )
	}

	return (
		<Modal
			title={ (
				<span className={ `${ ns }__modal-title` }>
					<span className="dashicons rm-icon-comments" aria-hidden="true" />
					{ __( 'Transcript', 'seo-by-rank-math' ) }
				</span>
			) }
			onRequestClose={ onClose }
			className={ `${ ns }__wrapper` }
			size="large"
		>
			<div className={ ns }>

				<div className={ `${ ns }__left` }>

					<div className={ `${ ns }__left-header` }>
						<Button
							variant="secondary"
							className={ `${ ns }__copy-btn` }
							onClick={ handleCopyText }
							iconLeft={ <span className="dashicons dashicons-clipboard" /> }
						>
							{ copiedText
								? __( 'Copied!', 'seo-by-rank-math' )
								: __( 'Copy Raw Text', 'seo-by-rank-math' )
							}
						</Button>
					</div>

					<div className={ `${ ns }__sections` }>
						<div className={ `${ ns }__section` }>
							<div className={ `${ ns }__section-left` }>
								<span className={ `${ ns }__section-icon` }>
									<span className="dashicons dashicons-admin-users" aria-hidden="true" />
								</span>
							</div>
							<div className={ `${ ns }__section-right` }>
								<div className={ `${ ns }__section-label` }>
									{ __( 'Original Query', 'seo-by-rank-math' ) }
								</div>
								<div className={ `${ ns }__content-box` }>
									{ entry.query || '—' }
								</div>
							</div>
						</div>

						<div className={ `${ ns }__section` }>
							<div className={ `${ ns }__section-left` }>
								<span className={ `${ ns }__section-icon` }>
									<span className="dashicons dashicons-superhero-alt" aria-hidden="true" />
								</span>
							</div>
							<div className={ `${ ns }__section-right` }>
								<div className={ `${ ns }__section-label` }>
									{ __( 'Model Response', 'seo-by-rank-math' ) }
								</div>
								<div className={ `${ ns }__content-box ${ ns }__content-box--response` }>
									{ entry.response || entry.excerpt || '—' }
								</div>
							</div>
						</div>
					</div>

				</div>

				<div className={ `${ ns }__right` }>

					<div className={ `${ ns }__right-header` }>
						<div className={ `${ ns }__right-title` }>
							<span className={ `${ ns }__json-badge` }>{ '{}' }</span>
							{ __( 'Extracted Signals', 'seo-by-rank-math' ) }
						</div>
						<Button
							variant="secondary"
							onClick={ handleCopyJson }
							className={ `${ ns }__copy-json-btn` }
							iconLeft={ <span className="dashicons dashicons-clipboard" /> }
						>
							{ copiedJson
								? __( 'Copied!', 'seo-by-rank-math' )
								: __( 'Copy JSON', 'seo-by-rank-math' )
							}
						</Button>
					</div>

					<div className={ `${ ns }__json-viewer` }>
						{ /* dangerouslySetInnerHTML is safe: content is our own serialised JSON */ }
						<pre
							className={ `${ ns }__json-pre` }
							// eslint-disable-next-line react/no-danger
							dangerouslySetInnerHTML={ {
								__html: highlightJson( JSON.stringify( prepareEntry( entry ), null, 2 ) ),
							} }
						/>
					</div>

				</div>

			</div>
		</Modal>
	)
}

TranscriptModal.displayName = 'TranscriptModal'

export default TranscriptModal
