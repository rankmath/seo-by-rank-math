/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import humanNumber from '@helpers/humanNumber'
import getFilterQuery from './Analytics/getFilterQuery'
import { getSnippetIcon } from '@helpers/snippetIcon'
import { processRows, filterShownHeaders } from './functions'
import AnalyticItem from './Dashboard/AnalyticItem'

addFilter(
	'rankMath.components.AnalyticItem',
	'rank-math',
	() => AnalyticItem
)

addFilter(
	'rank_math_filter_shown_headers',
	'rank-math',
	filterShownHeaders
)

addFilter(
	'rank_math_process_rows',
	'rank-math',
	processRows
)

addFilter(
	'rank_math_getFilterQuery',
	'rank-math',
	getFilterQuery
)

addFilter(
	'rank_math_humanNumber',
	'rank-math',
	humanNumber
)

addFilter(
	'rank_math_getSnippetIcon',
	'rank-math',
	getSnippetIcon
)
