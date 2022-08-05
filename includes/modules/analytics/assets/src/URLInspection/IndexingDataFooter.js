/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withFilters } from '@wordpress/components'

const IndexingDataFooter = () => {
	return (
		<div className="row-footer">
			<table>
				<tbody>
					<tr>
						<td colSpan="8">
							<div className="last-crawl-data">
								<div>
									<strong>{ __( 'Google: ', 'rank-math' ) }</strong>
									<span className="blurred">{ __( 'Available in the PRO version', 'rank-math' ) }</span>
								</div>
								<div>
									<strong>{ __( 'Last Crawl: ', 'rank-math' ) }</strong>
									<span className="blurred">{ __( 'PRO Feature', 'rank-math' ) }</span>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	)
}

export default withFilters( 'rankMath.analytics.IndexingDataFooter' )( IndexingDataFooter )
