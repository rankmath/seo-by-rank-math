/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch } from '@wordpress/data'
import { useState, Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Header from '@scShared/Header'
import StatFilter from './StatFilter'
import PositionFilter from './PositionFilter'
import KeywordsTable from './KeywordsTable'
import PositionGraph from './PositionGraph'
import KeywordGraph from './KeywordGraph'
import TopKeywords from './TopKeywords'

const Keywords = () => {
	const [ selected, setSelection ] = useState( {
		impressions: true,
		clicks: true,
		keywords: true,
		ctr: false,
		position: false,
	} )

	const [ position, setPosition ] = useState( {
		top3: true,
		top10: true,
		top50: true,
		top100: true,
	} )

	return (
		<Fragment>
			<Header
				heading={ __( 'Keywords', 'rank-math' ) }
				onChange={ () => {
					dispatch(
						'rank-math'
					).invalidateResolutionForStoreSelector(
						'getKeywordsOverview'
					)
					dispatch(
						'rank-math'
					).invalidateResolutionForStoreSelector( 'getKeywordsRows' )
					dispatch(
						'rank-math'
					).invalidateResolutionForStoreSelector(
						'getKeywordsSummary'
					)
				} }
			/>
			<PositionFilter
				selected={ position }
				setSelection={ setPosition }
			/>
			<PositionGraph selected={ position } />
			<StatFilter selected={ selected } setSelection={ setSelection } />
			<KeywordGraph selected={ selected } />
			<TopKeywords />
			<KeywordsTable />
		</Fragment>
	)
}

export default Keywords
