/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { useState, useEffect } from '@wordpress/element'
import { useCopyToClipboard } from '@wordpress/compose'

/**
 * Internal Dependencies
 */
import { Button, TextareaControl } from '@rank-math/components'

/**
 * Display Error Log section.
 *
 * @param {Object}  props               Component props.
 * @param {string}  props.errorLog      The last x rows from the error log.
 * @param {string}  props.errorLogPath  The error log file location.
 * @param {string}  props.errorLogSize  The error log size.
 * @param {boolean} props.errorLogError Show error if the log cannot be loaded.
 */
export default ( { errorLog, errorLogPath, errorLogSize, errorLogError } ) => {
	const [ isCopied, setIsCopied ] = useState( false )

	const ref = useCopyToClipboard( errorLog, () => {
		setIsCopied( true )

		setTimeout( () => {
			setIsCopied( false )
		}, 2000 )
	} )

	useEffect( () => {
		const textarea = document.getElementById( 'rank-math-status-error-log' )

		if ( textarea ) {
			textarea.scrollTop = textarea.scrollHeight
		}
	}, [] )

	return (
		<div className="rank-math-system-status rank-math-box">
			<header>
				<h3>{ __( 'Error Log', 'rank-math' ) }</h3>
			</header>

			<p
				className="description"
				dangerouslySetInnerHTML={ {
					__html: sprintf(
						// Translators: placeholder is a link to WP_DEBUG documentation.
						__(
							'If you have %s enabled, errors will be stored in a log file. Here you can find the last 100 lines in reversed order so that you or the Rank Math support team can view it easily. The file cannot be edited here.',
							'rank-math'
						),
						`<a href="https://wordpress.org/support/article/debugging-in-wordpress/" target=_blank" >WP_DEBUG_LOG</a>`
					),
				} }
			/>

			{ errorLogError && (
				<strong className="error-log-cannot-display">{ errorLogError }</strong>
			) }

			{ ! errorLogError && (
				<>
					<div className="copy-button-wrapper">
						<Button ref={ ref }>
							{ __( 'Copy Log to Clipboard', 'rank-math' ) }
						</Button>
						{ isCopied && (
							<span className="success">{ __( 'Copied!', 'rank-math' ) }</span>
						) }
					</div>

					<div id="error-log-wrapper">
						<TextareaControl
							rows={ 16 }
							cols={ 80 }
							value={ errorLog }
							variant="code-box"
							className="code"
							id="rank-math-status-error-log"
							disabled
						/>
					</div>

					{ errorLog && (
						<div className="error-log-info">
							<code>{ errorLogPath }</code>
							<em>&nbsp;({ errorLogSize })</em>
						</div>
					) }
				</>
			) }
		</div>
	)
}
