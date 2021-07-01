/**
 * External dependencies
 */
import { isEmpty } from 'lodash'
import { withRouter } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { Fragment } from '@wordpress/element'
import { withFilters } from '@wordpress/components'
import { decodeEntities } from '@wordpress/html-entities'

/**
 * Internal dependencies
 */
import Header from '@scShared/Header'

const Single = ( { post } ) => {
	if ( isEmpty( post ) ) {
		return null
	}

	return (
		<Fragment>
			<Header
				heading={ decodeEntities( post.title ) }
				slug={ post.page }
				adminURL={ post.admin_url }
				homeURL={ post.home_url }
			/>

			<div className="rank-math-single-post-report">
				<div id="rank-math-pro-cta" className="center">
					<div className="rank-math-cta-box blue-ticks width-50 top-20">
						<h3>{ __( 'Ready for more than just an overview? We have fully-fledged reports!', 'rank-math' ) }</h3>
						<ul>
							<li>{ __( 'Track more than 20 metrics for all of your posts', 'rank-math' ) }</li>
							<li>{ __( 'Monitor Google trends for your selected focus keyword', 'rank-math' ) }</li>
							<li>{ __( 'Keep an eye on the data that matters all in one place', 'rank-math' ) }</li>
						</ul>
						<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Single%20Post%20Report&utm_campaign=WP" target="_blank" rel="noreferrer" className="button button-primary is-green">{ __( 'Upgrade', 'rank-math' ) }</a>
					</div>
				</div>
				<img src={ rankMath.singleImage } alt={ __( 'Single Post/Page Reports', 'rank-math' ) } className="single-post-report" />
			</div>

		</Fragment>
	)
}

export default withRouter( withFilters( 'rankMath.analytics.single' )(
	withSelect( ( select, props ) => {
		const { id = 0 } = props.match.params

		return {
			...props,
			post: select( 'rank-math' ).getSinglePost( id ),
		}
	} )( Single )
) )
