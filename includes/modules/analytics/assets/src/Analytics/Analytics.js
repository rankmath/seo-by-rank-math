/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { dispatch } from '@wordpress/data'
import { Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import ScoreBar from './ScoreBar'
import Header from '@scShared/Header'
import PostsTable from './PostsTable'
import ScoreFilter from './ScoreFilter'

const Analytic = () => {
	return (
		<Fragment>
			<Header
				heading={ __( 'Site Analytics', 'rank-math' ) }
				onChange={ () => {
					dispatch(
						'rank-math'
					).invalidateResolutionForStoreSelector( 'getPostsSummary' )
					dispatch(
						'rank-math'
					).invalidateResolutionForStoreSelector( 'getPostsRowsByObjects' )
				} }
			/>
			<ScoreBar />
			<ScoreFilter />
			<PostsTable />
		</Fragment>
	)
}

export default Analytic
