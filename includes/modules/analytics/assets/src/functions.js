/**
 * External dependencies
 */
import classnames from 'classnames'
import { get, has, map, isArray } from 'lodash'
import { Link } from 'react-router-dom'
import {
	AreaChart,
	Area,
	YAxis,
	Tooltip as ChartTooltip,
	ResponsiveContainer,
} from 'recharts'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { select } from '@wordpress/data'
import { Fragment } from '@wordpress/element'
import { Button } from '@wordpress/components'
import { applyFilters, doAction } from '@wordpress/hooks'
import { decodeEntities } from '@wordpress/html-entities'

/**
 * Internal dependencies
 */
import ItemStat from '@scShared/ItemStat'
import LinkListing from '@scShared/LinkListing'
import ScoreProgress from '@scShared/ScoreProgress'
import SchemaListing from '@scShared/SchemaListing'
import ActionListing from '@scShared/ActionListing'
import KeywordButton from '@scShared/KeywordButton'
import KeywordTitle from '@scShared/KeywordTitle'

/**
 * Check if PRO version is installed.
 *
 * @return {boolean} Return true if PRO version is intalled, otherwise return false.
 */
export function isPro() {
	return applyFilters( 'rank_math_is_pro', false )
}

/**
 * Filter out header items.
 *
 * @param {Array} headers Header Items object to be filtered.
 * @param {Array} hiddenKeys Configration object to show/hide specific header items.
 *
 * @return {Array} Filtered Header Items.
 */
export function filterShownHeaders( headers, hiddenKeys ) {
	return map( headers, ( header ) => ( {
		...header,
		visible:
			header.required ||
			( has( hiddenKeys, header.key ) && hiddenKeys[ header.key ] ),
	} ) )
}

/**
 * Captialize the first letter of the input string.
 *
 * @param {string} str String to convert.
 *
 * @return {string} Converted string.
 */
export function Capitalize( str ) {
	return str.charAt( 0 ).toUpperCase() + str.slice( 1 )
}

/**
 * Get offset of the page.
 *
 * @param {number} page The page number.
 * @param {number} perPage The item count per page.
 *
 * @return {number} Offest of the page.
 */
export function getPageOffset( page, perPage ) {
	return ( page - 1 ) * perPage
}

/**
 * Get Post Type Icon.
 *
 * @param {string} type Post Type.
 *
 * @return {string} Icon class name.
 */
function getPostTypeIcons( type ) {
	const icons = {
		post: 'rm-icon-post',
		page: 'rm-icon-page',
		product: 'rm-icon-cart', // WooCommerce.
		download: 'rm-icon-cart', // Easy Digital Download.
		'web-story': 'rm-icon-stories', // Google Web Stories.
		topic: 'rm-icon-users', // bbPress topic.
	}

	return classnames(
		'post-type rm-icon',
		has( icons, type ) ? icons[ type ] : 'rm-icon-post'
	)
}

/**
 * Process Table Rows Data and Render.
 *
 * @param {Array} rows Table Rows Data.
 * @param {Array} columns Table Header Data.
 * @param {number} offset Offset of Rows Data.
 * @param {Array} trackedKeywords Tracked Keywords Data.
 */
export function processRows( rows, columns, offset = 0, trackedKeywords ) {
	let counter = 0

	return map( rows, ( row, rowID ) =>
		map( columns, ( column ) => {
			let value = get( row, column, '' )
			let display = ''

			if ( 'sequenceOnly' === column ) {
				display = ++counter + offset
			} else if ( 'sequence' === column ) {
				value = get( row, 'object_subtype', 'post' )
				display = (
					<Fragment>
						{ ++counter + offset }{ ' ' }
						<i
							className={ getPostTypeIcons( value ) }
							title={ Capitalize( value ) }
						/>
					</Fragment>
				)
			} else if ( 'sequenceAdd' === column ) {
				const isTracked =
					trackedKeywords && trackedKeywords.includes( rowID )
				display = <KeywordButton
					isTracked={ isTracked }
					sequence={ ++counter + offset }
					query={ row.query }
				/>
			} else if ( 'sequenceDelete' === column ) {
				display = <Fragment>
					{ ++counter + offset }
					<Button
						className="button button-secondary button-small add-keyword delete"
						title={ __( 'Delete from Keyword Manager', 'rank-math' ) }
						onClick={ () => doAction( 'rank_math_remove_keyword', row.query ) }
					>
						<i className="rm-icon rm-icon-trash" />
					</Button>
				</Fragment>
			} else if ( 'title' === column ) {
				value = value || rowID
				display = (
					<h4>
						<Link to={ '/single/' + get( row, 'object_id', '' ) }>
							<span>{ decodeEntities( value ) }</span>
							<small>{ row.page }</small>
						</Link>
					</h4>
				)
			} else if ( 'query' === column ) {
				display = <KeywordTitle query={ value } />
			} else if ( 'seo_score' === column ) {
				display = <ScoreProgress score={ value } />
			} else if ( 'schemas_in_use' === column ) {
				display = <SchemaListing schemas={ value } />
				value = isArray( value ) ? value.join( ' ' ) : ''
			} else if (
				'impressions' === column ||
				'pageviews' === column ||
				'clicks' === column ||
				'ctr' === column
			) {
				// Display difference status of Analytics Data for each table row.
				display = <ItemStat { ...value } />
				value = value.difference
			} else if ( 'position' === column ) {
				// Display difference status of Position Value for each table row.
				display = <ItemStat { ...value } revert={ true } />
				value = value.difference
			} else if ( 'positionHistory' === column ) {
				// Display Position History Graph for each table row.
				const graph = get( row, 'graph', false )
				let baseValue = 'dataMax'
				if ( graph !== false && isArray( graph ) ) {
					const dataMax = Math.max( ...graph.map( ( item ) => item.position ) )
					baseValue = Math.min( dataMax + parseInt( dataMax / 2 ), 100 )
				}
				display =
					false === graph ? (
						''
					) : (
						<div className="rank-math-graph">
							<ResponsiveContainer height={ 40 }>
								<AreaChart
									data={ graph }
									baseValue={ baseValue }
									margin={ {
										top: 0,
										right: 0,
										left: 0,
										bottom: 0,
									} }
								>
									<ChartTooltip
										wrapperClassName="rank-math-graph-tooltip"
										labelFormatter={ ( index ) =>
											get(
												graph,
												[ index, 'formatted_date' ],
												''
											)
										}
									/>
									<defs>
										<linearGradient
											id="gradient"
											x1="0"
											y1="0"
											x2="0"
											y2="1"
										>
											<stop
												offset="5%"
												stopColor="#4e8cde"
												stopOpacity={ 0.2 }
											/>
											<stop
												offset="95%"
												stopColor="#4e8cde"
												stopOpacity={ 0 }
											/>
										</linearGradient>
									</defs>
									<Area
										dataKey="position"
										stroke="#4e8cde"
										strokeWidth={ 2 }
										fill="url(#gradient)"
									/>
									<YAxis
										hide={ true }
										reversed={ true }
									/>
								</AreaChart>
							</ResponsiveContainer>
						</div>
					)
			} else if ( 'links' === column ) {
				display = <LinkListing links={ value } />
				value = ''
			} else if ( 'actions' === column ) {
				display = <ActionListing actions={ value } />
				value = value.join( ' ' )
			}

			return { display, value }
		} )
	)
}

export function generateTicks( data ) {
	const ticks = []
	const dataLength = data.length
	const days = select( 'rank-math' ).getDaysRange()
	const tickCount = {
		'-7 days': 5,
		'-15 days': 3,
		'-30 days': 3,
		'-3 months': 11,
		'-6 months': 4,
		'-1 year': 10,
	}

	for ( let i = 1; i <= tickCount[ days ]; i++ ) {
	}

	ticks.push( data[ 0 ].date )
	ticks.push( data[ dataLength - 1 ].date )

	return ticks
}

/**
 * Construct URL parameter.
 *
 * @param {Object} filters URL parameter items.
 * @param {boolean} booleanMode Mode to set parameter value as boolean type.
 *
 * @return {string} Constructed URL parameter.
 */
export function filtersToUrlParams( filters, booleanMode = true ) {
	let params = ''
	map( filters, ( val, key ) => {
		if ( val ) {
			params += '&' + key + '=' + ( true === booleanMode ? '1' : val )
		}
	} )
	return params
}

/**
 * Convert difference values as difference view.
 *
 * @param {Object} items Object to convert.
 *
 * @return {Object} Converted object.
 */
export function convertNumbers( items ) {
	return map( items, ( item ) => {
		item.title = item.query
		item.content = <ItemStat { ...item.position } />
		return item
	} )
}
