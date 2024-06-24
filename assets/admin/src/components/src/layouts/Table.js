/**
 * External dependencies
 */
import { map } from 'lodash'
import classNames from 'classnames'

/**
 * Internal dependencies
 */
import './scss/Table.scss'

/**
 * Table component.
 *
 * @param {Object}  props           Component props.
 * @param {Array}   props.data      Items to be rendered into the table.
 * @param {Array}   props.columns   The table heading.
 * @param {string}  props.className CSS class for addtional styling.
 * @param {boolean} props.variant   Specifies the table's style. Accepted value: "wizard".
 */
export default ( {
	data,
	columns,
	variant,
	className,
	...additionalProps
} ) => {
	className = classNames( variant, className, 'rank-math-table' )

	const tableProps = {
		...additionalProps,
		className,
	}

	if ( variant === 'wizard' ) {
		return (
			<table { ...tableProps }>
				<tbody>
					{ map( data, ( { field, passed, warning, ...thProps }, index ) => (
						<tr key={ index }>
							<th
								{ ...thProps }
								className={ `
									${ passed ? 'is-pass' : 'is-fail' } 
									${ warning ? 'is-warning' : '' }
								` }
							>
								{ field }
							</th>
						</tr>
					) ) }
				</tbody>
			</table>
		)
	}

	return (
		<table { ...tableProps }>
			{ columns && (
				<thead>
					<tr>
						{ map( columns, ( { field, ...thProps } ) => (
							<th { ...thProps }>{ field }</th>
						) ) }
					</tr>
				</thead>
			) }

			<tbody>
				{ map( data, ( row, index ) => (
					<tr key={ index }>
						{ columns
							? map( columns, ( { key } ) => (
								<td key={ key }>{ row[ key ] }</td>
							) )
							: map( row, ( { field, ...tdProps }, tdIndex ) => (
								<td { ...tdProps } key={ tdIndex }>
									{ field }
								</td>
							) ) }
					</tr>
				) ) }
			</tbody>
		</table>
	)
}
