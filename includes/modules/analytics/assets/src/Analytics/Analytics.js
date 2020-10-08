/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { dispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import ScoreBar from './ScoreBar'
import Header from '@scShared/Header'
import ScoreFilter from './ScoreFilter'
import PostsTable from './PostsTable'

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
