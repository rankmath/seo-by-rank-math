/**
 * DataTable — generic data table for the AI Visibility module.
 *
 * @since 1.0.273
 */

/**
 * Internal dependencies
 */
import './DataTable.scss'

/**
 * DataTable component.
 *
 * @param {Object}          props
 * @param {Array}           props.columns     Column definitions (see above).
 * @param {Array}           props.rows        Row data objects.
 * @param {string|Function} [props.rowKey]    Key name or function to derive a unique key per row. Default 'id'.
 * @param {string}          [props.className] Extra class appended to the <table> element.
 * @return {JSX.Element} Rendered data table.
 */
const DataTable = ( { columns = [], rows = [], rowKey = 'id', className = '' } ) => {
	const ns = 'rank-math-ai-visibility-data-table'

	const tableClass = [ ns, className ].filter( Boolean ).join( ' ' )

	const getRowKey = typeof rowKey === 'function'
		? rowKey
		: ( row ) => row[ rowKey ]

	return (
		<table className={ tableClass }>
			<thead>
				<tr>
					{ columns.map( ( col ) => (
						<th
							key={ col.key }
							className={ `${ ns }__th` }
							style={ col.width ? { width: col.width } : undefined }
						>
							{ col.label }
						</th>
					) ) }
				</tr>
			</thead>
			<tbody>
				{ rows.map( ( row, i ) => (
					<tr key={ getRowKey( row, i ) } className={ `${ ns }__row` }>
						{ columns.map( ( col ) => (
							<td key={ col.key } className={ `${ ns }__td ${ ns }__td--${ col.key }` }>
								{ col.render ? col.render( row ) : ( row[ col.key ] ?? '—' ) }
							</td>
						) ) }
					</tr>
				) ) }
			</tbody>
		</table>
	)
}

DataTable.displayName = 'DataTable'

export default DataTable
