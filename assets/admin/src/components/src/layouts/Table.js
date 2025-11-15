/**
 * External dependencies
 */
import { map, slice } from 'lodash'
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { isValidElement, RawHTML } from '@wordpress/element'

/**
 * Internal dependencies
 */
import './scss/Table.scss'

/**
 * Table component.
 *
 * @param {Object}  props           Component props
 * @param {string}  props.size      The size of the table. Accepted value is 'small'
 * @param {string}  props.type      Table type. Eg: striped.
 * @param {Object}  props.fields    The table fields in the format of {label: value}
 * @param {string}  props.className CSS class for addtional styling
 * @param {boolean} props.addHeader If true, renders the first table column as a table heading
 * @param {boolean} props.useThOnly If true, th is used instead of td.
 */
export default ( { fields, size, type = 'striped', className, addHeader = true, useThOnly, ...additionalProps } ) => {
	className = classNames(
		'rank-math-table wp-list-table widefat',
		className,
		size,
		{
			striped: type === 'striped',
		}
	)

	const header = addHeader ? fields[ 0 ] : null
	const bodyRows = addHeader ? slice( fields, 1 ) : fields
	const CellTag = useThOnly ? 'th' : 'td'
	const columnCount = addHeader ? header.length : Math.max( ...fields.map( ( row ) => row.length ) )

	return (
		<table className={ className } { ...additionalProps }>
			<thead>
				<tr className="bg-gray-100">
					{
						map( header, ( cell, index ) => (
							<th key={ index }>
								{ cell }
							</th>
						) )
					}
				</tr>
			</thead>
			<tbody>
				{
					map( bodyRows, ( row, rowIndex ) => {
						const rowLength = row.length
						const lastIndex = rowLength - 1
						const needsColspan = rowLength < columnCount
						return (
							<tr key={ rowIndex }>
								{ map( row, ( cell, cellIndex ) => {
									const isLastCell = cellIndex === lastIndex && needsColspan
									return (
										<CellTag
											key={ cellIndex }
											colSpan={ isLastCell ? columnCount - cellIndex : undefined }
										>
											{ isValidElement( cell ) ? cell : <RawHTML>{ cell }</RawHTML> }
										</CellTag>
									)
								} ) }
							</tr>
						)
					} )
				}
			</tbody>
		</table>
	)
}
