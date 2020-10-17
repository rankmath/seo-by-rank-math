/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch } from '@wordpress/data'
import { Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Header from '@scShared/Header'
import SeoScoreOverview from './SeoScoreOverview'
import AnalyticsOverview from './AnalyticsOverview'
import KeywordsOverview from './KeywordsOverview'
import KeywordsPositionOverview from './KeywordsPositionOverview'
import TopActionablePosts from './TopActionablePosts'
import TopPosts from './TopPosts'

const Dashbaord = () => {
	return (
		<Fragment>
			<Header
				heading={ __( 'Analytics', 'rank-math' ) }
				onChange={ () => {
					dispatch(
						'rank-math'
					).invalidateResolutionForStoreSelector( 'getPostsOverview' )
					dispatch(
						'rank-math'
					).invalidateResolutionForStoreSelector(
						'getKeywordsOverview'
					)
				} }
			/>

			<div className="grid">
				<SeoScoreOverview />
				<AnalyticsOverview />
			</div>

			<div className="grid">
				<KeywordsOverview />
				<KeywordsPositionOverview />
			</div>

			<TopPosts />
			<TopActionablePosts />
		</Fragment>
	)
}

export default Dashbaord
