/**
 * External dependencies
 */
import classnames from 'classnames'
import { map, forEach, isUndefined, kebabCase, startCase, camelCase, lowerCase, includes, uniqueId, unescape } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { addFilter } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import SchemaListing from '@scShared/SchemaListing'
import { getSnippetIcon } from '@helpers/snippetIcon'
import { isPro } from '../functions'
import { convertValue } from '../helpers'

addFilter( 'rank_math_table_column_value', 'rank-math', ( data, display, value, column ) => {
	if ( includes( [ 'index_verdict', 'indexing_state', 'mobile_usability_verdict', 'crawled_as', 'robots_txt_state' ], column ) ) {
		const newValue = kebabCase( value )
		let className = column + ' verdict '
		if ( 'index_verdict' !== column || isPro() ) {
			className += classnames(
				lowerCase( newValue ),
				{
					unspecified: includes( [ 'verdict-unspecified', 'indexing-state-unspecified', 'crawling-user-agent-unspecified', 'robots-txt-state-unspecified' ], newValue ),
					pass: includes( [ 'pass', 'indexing-allowed', 'allowed' ], newValue ),
					fail: includes( [ 'fail', 'blocked-by-meta-tag' ], newValue ),
				}
			)
		}

		display = (
			<span className="rank-math-tooltip">
				<i className={ className }></i>
				<span>{ startCase( camelCase( convertValue( value ) ) ) }</span>
			</span>
		)

		return { display, value }
	}

	if ( 'rich_results_items' === column ) {
		if ( value ) {
			display = (
				<div className="schema-listing">
					{ map( JSON.parse( value ), ( schema ) => {
						const type = unescape( schema.richResultType )
						const icon = type.replace( / /g, '' )
						let schemaClass = 'schema-item'
						let errorType = __( 'Pass', 'rank-math' )

						if ( ! isUndefined( schema.items[ 0 ].issues ) ) {
							let itemClass = ''
							forEach( schema.items[ 0 ].issues, ( issue ) => {
								if ( 'ERROR' === issue.severity ) {
									itemClass = issue.severity
									errorType = __( 'Error', 'rank-math' )
									return false
								}
								itemClass = issue.severity
								errorType = issue.severity
							} )

							schemaClass += ' ' + lowerCase( itemClass )
						}

						return (
							<span className={ schemaClass } key={ uniqueId( 'schema-' ) }>
								<span className="rank-math-tooltip">
									<i className={ getSnippetIcon( icon ) } />
									<span>{ errorType }</span>
								</span>
								{ type }
							</span>
						)
					} ) }
				</div>
			)
		} else {
			display = <SchemaListing schemas={ value } />
		}

		return { display, value }
	}

	return data
} )
