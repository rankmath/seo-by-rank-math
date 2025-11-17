/**
 * External Dependencies
 */
import { map, entries, isEmpty } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal Dependencies
 */
import { Button, CheckboxList } from '@rank-math/components'

const panelOptions = {
	general: __( 'General Settings', 'rank-math' ),
	titles: __( 'Titles & Metas', 'rank-math' ),
	sitemap: __( 'Sitemap Settings', 'rank-math' ),
	'role-manager': __( 'Role Manager Settings', 'rank-math' ),
	redirections: __( 'Redirections', 'rank-math' ),
}

/**
 * Export Rank Math settings
 */
export default () => {
	const [ settings, setSettings ] = useState( Object.keys( panelOptions ) )
	const choiceOptions = map(
		entries( panelOptions ),
		( [ id, label ] ) => ( { id, label } )
	)
	return (
		<div
			id="rank-math-export-form"
			className="rank-math-export-form field-form"
		>
			<CheckboxList
				variant="default"
				value={ settings }
				onChange={ setSettings }
				options={ choiceOptions }
			/>

			<p className="description">
				{ __( 'Choose the panels to export.', 'rank-math' ) }
			</p>

			<footer>
				<Button
					variant="primary"
					disabled={ isEmpty( settings ) }
					onClick={ () => {
						apiFetch( {
							method: 'POST',
							headers: {},
							path: '/rankmath/v1/status/exportSettings',
							data: {
								panels: settings,
							},
						} )
							.catch( ( error ) => {
								alert( error.message )
							} )
							.then( ( response ) => {
								const blob = new Blob( [ response ], { type: 'application/json' } )
								const url = URL.createObjectURL( blob )

								const a = document.createElement( 'a' )
								a.href = url
								a.download = `rank-math-settings-${ new Date().toISOString().replace( /[:.]/g, '-' ) }.json`
								document.body.appendChild( a )
								a.click()

								document.body.removeChild( a )
								URL.revokeObjectURL( url )
							} )
					} }
				>
					{ __( 'Export', 'rank-math' ) }
				</Button>
			</footer>
		</div>
	)
}
