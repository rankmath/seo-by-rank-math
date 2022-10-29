/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'
import { withFilters } from '@wordpress/components'

/**
 * Internal dependencies
 */
import IndexingTable from './IndexingTable'
import './functions'
import { isPro, withRouter } from '../functions'
import getLink from '@helpers/getLink'

const UrlInspection = () => {
	return (
		<Fragment>
			{
				! isPro() &&
				<div className="rank-math-unlock-pro-notice" id="rank-math-unlock-pro-notice">
					<a href={ getLink( 'pro', 'Unlock PRO Index Status Tab Notice' ) } target="_blank" className="pro-link" rel="noreferrer">
						<p>
							{ __( 'Get Advanced Index Stats Directly from Google database.', 'rank-math' ) } <span><strong>{ __( 'Upgrade to Rank Math PRO!', 'rank-math' ) }</strong></span>
						</p>
					</a>
				</div>
			}
			<IndexingTable />
		</Fragment>
	)
}

export default withRouter( withFilters( 'rankMath.analytics.UrlInspection' )( UrlInspection ) )
