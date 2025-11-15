/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import { useCopyToClipboard } from '@wordpress/compose'

/**
 * Internal Dependencies
 */
import { Button } from '@rank-math/components'
import SystemStatusAccordion from './SystemStatusAccordion'

/**
 * Display system details.
 *
 * @param {Object} props                Component props.
 * @param {Object} props.systemInfo     The system information in object format.
 * @param {string} props.systemInfoCopy The system information in string format for copy.
 */
export default ( { systemInfo, systemInfoCopy } ) => {
	const [ isCopied, setIsCopied ] = useState( false )
	const ref = useCopyToClipboard( systemInfoCopy, () => {
		setIsCopied( true )

		setTimeout( () => {
			setIsCopied( false )
		}, 3000 )
	} )

	return (
		<div className="rank-math-system-status rank-math-box">
			<header>
				<h3>{ __( 'System Info', 'rank-math' ) }</h3>
			</header>

			<div className="copy-button-wrapper">
				<Button ref={ ref }>
					{ __( 'Copy System Info to Clipboard', 'rank-math' ) }
				</Button>
				{ isCopied && (
					<span className="success">{ __( 'Copied!', 'rank-math' ) }</span>
				) }
			</div>

			<SystemStatusAccordion { ...systemInfo } />
		</div>
	)
}
