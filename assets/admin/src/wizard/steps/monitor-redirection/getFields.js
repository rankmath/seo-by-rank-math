/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import getLink from '@helpers/getLink'

export default () => {
	return [
		{
			id: '404_monitor_title',
			type: 'raw',
			content: (
				<div
					key="monitor-header"
					className="field-row monitor-header text-center"
				>
					<h1>{ __( '404 Monitor', 'rank-math' ) }</h1>
					<div className="monitor-desc text-center">
						{ __(
							'Set default values for the 404 error monitor here.',
							'rank-math'
						) }
					</div>
				</div>
			),
		},
		{
			id: '404-monitor',
			type: 'toggle',
			name: __( '404 Monitor', 'rank-math' ),
			desc: __(
				'The 404 monitor will let you see if visitors or search engines bump into any <code>404 Not Found</code> error while browsing your site.',
				'rank-math'
			),
		},
		{
			id: 'redirection_title',
			type: 'raw',
			content: (
				<div
					key="redirections-header"
					className="field-row redirections-header text-center"
					style={ { borderTop: 0 } }
				>
					<br />
					<h1>{ __( 'Redirections', 'rank-math' ) }</h1>
					<div className="redirections-desc text-center">
						{ __(
							'Set default values for the redirection module from here. ',
							'rank-math'
						) }
						<a
							target="_blank"
							rel="noreferrer"
							href={ getLink( 'redirections', 'SW Redirection Step' ) }
						>
							{ __( 'Learn more about Redirections.', 'rank-math' ) }
						</a>
					</div>
				</div>
			),
		},
		{
			id: 'redirections',
			type: 'toggle',
			name: __( 'Redirections', 'rank-math' ),
			desc: __(
				'Set up temporary or permanent redirections. Combined with the 404 monitor, you can easily redirect faulty URLs on your site, or add custom redirections.',
				'rank-math'
			),
		},
	]
}
