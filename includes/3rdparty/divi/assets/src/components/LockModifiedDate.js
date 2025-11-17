/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect } from '@wordpress/element'
import { dispatch } from '@wordpress/data'
import { Button, Dashicon } from '@wordpress/components'

export default ( props ) => {
	const [ isOpen, setOpen ] = useState( false )

	useEffect( () => {
		dispatch( 'rank-math' ).lockModifiedDate( false )
	}, [] )

	return (
		<>
			<Button
				className="et-fb-button--success"
				variant="tertiary"
				onClick={ () => ( setOpen( ! isOpen ) ) }
			>
				<Dashicon icon={ isOpen ? 'arrow-down-alt2' : 'arrow-up-alt2' } />
			</Button>

			{ isOpen && <Button
				className="et-fb-button--success rank-math-lock-modified-date"
				variant="tertiary"
				onClick={ () => {
					dispatch( 'rank-math' ).lockModifiedDate( true )
					const { conditionalTags } = window.ET_Builder.Frames.app.frameElement.contentWindow.ETBuilderBackend
					conditionalTags.lock_modified_date = true
					props.publishButton.click()
					setOpen( false )

					setTimeout( () => {
						delete conditionalTags.lock_modified_date
						dispatch( 'rank-math' ).lockModifiedDate( false )
					}, 3000 )
				} }
			>
				{ __( 'Save (Lock Modified Date)', 'rank-math' ) }
			</Button> }
		</>
	)
}
