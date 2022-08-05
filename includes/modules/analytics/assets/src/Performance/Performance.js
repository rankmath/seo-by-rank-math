/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch, withSelect } from '@wordpress/data'
import { useState, Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { isPro } from '../functions'
import Header from '@scShared/Header'
import StatFilter from './StatFilter'
import PostsTable from './PostsTable'
import PerformanceGraph from './Graph'
import TopPosts from './../Dashboard/TopPosts'

const Performance = ( { stats } ) => {
	const [ selected, setSelection ] = useState( {
		pageviews: isPro(),
		impressions: true,
		clicks: ! isPro(),
		keywords: true,
		ctr: false,
		position: false,
		adsense: false,
	} )

	return (
		<Fragment>
			<Header
				heading={ __( 'SEO Performance', 'rank-math' ) }
				onChange={ () => {
					dispatch(
						'rank-math'
					).invalidateResolutionForStoreSelector( 'getPostsSummary' )
					dispatch(
						'rank-math'
					).invalidateResolutionForStoreSelector( 'getPostsOverview' )
				} }
			/>
			<StatFilter
				stats={ stats }
				selected={ selected }
				setSelection={ setSelection }
			/>
			<PerformanceGraph stats={ stats } selected={ selected } />
			<TopPosts />
			<PostsTable />
		</Fragment>
	)
}

export default withSelect( ( select ) => {
	return {
		stats: select( 'rank-math' ).getDashboardStats(
			select( 'rank-math' ).getDaysRange()
		).stats,
	}
} )( Performance )
