/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'

const ActionListing = ( { actions } ) => {
	return (
		<Button className="button button-secondary">
			{ __( 'Suggested Actions', 'rank-math' ) }
			<span>{ actions.length }</span>
		</Button>
	)
}

export default ActionListing
