/**
 * External Dependencies
 */
import { entries, isEmpty, map } from 'lodash'

/**
 * WordPress Dependencies
 */
import { Panel, PanelBody, PanelRow } from '@wordpress/components'

/**
 * Internal Dependencies
 */
import { Table } from '@rank-math/components'

/**
 * Display list for system info.
 *
 * @param {Object} data
 */
export default ( data ) => {
	return (
		<Panel className="rank-math-panel">
			{ map( data, ( { label, fields, show_count: showCount }, index ) => {
				if ( isEmpty( fields ) ) {
					return
				}

				const countValue = `(${ entries( fields ).length })`

				const title = `${ label } ${ showCount ? countValue : '' }`

				return (
					<PanelBody key={ index } title={ title } initialOpen={ false }>
						<PanelRow>
							<Table
								size="small"
								fields={
									map( fields, ( field ) => {
										return [ field.label, field.value ]
									} )
								}
								addHeader={ false }
							/>
						</PanelRow>
					</PanelBody>
				)
			} ) }
		</Panel>
	)
}
