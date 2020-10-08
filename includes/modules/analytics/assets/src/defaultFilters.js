/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks'

import humanNumber from '@helpers/humanNumber'
import { processRows, filterShownHeaders, addKeyword, removeKeyword } from './functions'
import getFilterQuery from './Analytics/getFilterQuery'
import { getSnippetIcon } from '@helpers/snippetIcon'

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
	'rank_math_addKeyword',
	'rank-math',
	addKeyword
)

addFilter(
	'rank_math_removeKeyword',
	'rank-math',
	removeKeyword
)

addFilter(
	'rank_math_getSnippetIcon',
	'rank-math',
	getSnippetIcon
)
