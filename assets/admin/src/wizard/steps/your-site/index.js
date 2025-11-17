/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { Button } from '@rank-math/components'
import TabContent from '@rank-math-settings/components/TabContent'
import HelpPanel from './HelpPanel'
import getFields from './getFields'

export default ( { data, updateData } ) => {
	const [ showPanel, setShowPanel ] = useState( false )

	const fields = map( getFields( data ), ( field ) => {
		field.value = data[ field.id ]
		if ( field.type === 'file' ) {
			field.onChange = ( val ) => {
				updateData( field.id, val.url )
				updateData( field.id + '_id', val.id )
				return true
			}
		} else {
			field.onChange = ( val ) => ( updateData( field.id, val ) )
		}
		return field
	} )

	return (
		<>

			{ ! data.isWhitelabel && (
				<div className="rank-math-wizard-tutorial">
					<header>
						{ __( 'If you are new to Rank Math,', 'rank-math' ) }
						&nbsp;
						<Button
							variant="link"
							onClick={ () => setShowPanel( ( prev ) => ! prev ) }
						>
							{ __( 'click here', 'rank-math' ) }
						</Button>
						&nbsp;
						{ __( 'to learn more.', 'rank-math' ) }
					</header>

					{ showPanel && <HelpPanel /> }
				</div>
			) }

			<TabContent fields={ fields } settings={ data } />
		</>
	)
}
