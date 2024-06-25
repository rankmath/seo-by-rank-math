/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { Fragment, useState } from '@wordpress/element'
import { PanelBody } from '@wordpress/components'

/*
* Internal dependencies
*/
import KeywordField from '../research/KeywordField'
import ResearchContent from '../research/ResearchContent'
import ErrorCTA from '@components/ErrorCTA'

const getDummyData = () => {
	return {
		keywords: {
			content: {
				'rank math': { keyword: 'rank math', average: 17, count: 12 },
				'rank math vs yoast seo': { keyword: 'rank math vs yoast seo', average: 1, count: 1 },
				'what is rank math': { keyword: 'what is rank math', average: 1, count: 1 },
				'rank math schema': { keyword: 'rank math schema', average: 1, count: 1 },
				'rank math configuration': { keyword: 'rank math configuration', average: 1, count: 1 },
				'rank math pro version': { keyword: 'rank math pro version', average: 1, count: 2 },
				'rank math comparison': { keyword: 'rank math comparison', average: 1, count: 1 },
				'rank math for seo': { keyword: 'rank math for seo', average: 1, count: 1 },
				'seo by rank math': { keyword: 'seo by rank math', average: 1, count: 0 },
			},
		},
		related_keywords: [
			'rank math plugin',
			'rank math pricing',
			'rank math vs yoast',
			'rank math review',
			'rank math premium',
			'how to use rank math',
			'rank math training',
			'rank math woocommerce',
			'wordpress seo plugin',
		],
		recommendations: {
			wordCount: 1829,
			linkCount: { total: 16 },
			headingCount: { total: 9 },
			mediaCount: { total: 18 },
		},
	}
}

/*
* Research Tab.
*/
export default ( props ) => {
	const [ loading, setLoading ] = useState( false )
	const { data, updateData } = props
	let researchedData = data.researchedData

	const isFree = isEmpty( data.plan ) || data.plan === 'free'
	const hasCredits = data.isUserRegistered && data.credits >= 500
	const showError = isEmpty( researchedData ) && ( ! hasCredits || isFree )
	if ( showError ) {
		researchedData = getDummyData()
	}

	return (
		<Fragment>
			<PanelBody className="rank-math-content-ai-wrapper research" initialOpen={ true }>
				<KeywordField data={ data } updateData={ updateData } hasCredits={ hasCredits } isFree={ isFree } loading={ loading } setLoading={ setLoading } showError={ showError } />
				<ResearchContent { ...props } researchedData={ researchedData } loading={ loading } showError={ showError } />
				{ showError && <ErrorCTA isResearch={ true } /> }
			</PanelBody>
		</Fragment>
	)
}
