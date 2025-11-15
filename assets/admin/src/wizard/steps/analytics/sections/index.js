/**
 * External dependencies
 */
import { isEmpty, map } from 'lodash'
import classNames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import SearchConsole from './SearchConsole'
import Analytics from './Analytics'
import AdSense from './AdSense'
import ConnectActions from './ConnectActions'
import PermissionsWarning from './PermissionsWarning'

export default ( { data, updateData } ) => {
	const [ testConnection, setTestConnection ] = useState( {} )
	data.isConsoleConnected = ! isEmpty( data.searchConsole.profile )
	data.isAnalyticsConnected = ! isEmpty( data.analyticsData.view_id )
	data.isAdsenseConnected = ! isEmpty( data.analyticsData.adsense_id )
	const { hasConsolePermission, hasAnalyticsPermission, hasAdsensePermission } = data
	const sections = [
		{
			id: 'search-console',
			connected: rankMath.isConsoleConnected,
			disabled: ! hasConsolePermission,
			title: __( 'Search Console', 'rank-math' ),
			view: SearchConsole,
		},
		{
			id: 'analytics',
			connected: rankMath.isAnalyticsConnected,
			disabled: ! hasAnalyticsPermission,
			title: __( 'Analytics', 'rank-math' ),
			view: Analytics,
		},
		{
			id: 'adsense',
			connected: rankMath.isAdsenseConnected,
			disabled: rankMath.isPro && ! hasAdsensePermission,
			title: __( 'AdSense', 'rank-math' ),
			view: AdSense,
		},
	]

	return (
		<>
			{ rankMath.nextFetch && <span className="next-fetch">{ rankMath.nextFetch }</span> }

			<ConnectActions data={ data } testData={ testConnection } setTestConnection={ setTestConnection } />
			{
				map( sections, ( { id, connected, disabled, title, view: View } ) => {
					const isTestingConnection = ! isEmpty( testConnection[ id ] ) && testConnection[ id ] === 'loading'
					const testConnectionFailed = ! isEmpty( testConnection[ id ] ) && testConnection[ id ] === 'failed'
					const wrapperClass = classNames(
						`rank-math-box no-padding rank-math-accordion rank-math-connect-${ id }`,
						{
							connected,
							disconnected: ! connected,
							disabled,
						}
					)

					const statusClasses = classNames( 'rank-math-connection-status', {
						'rank-math-connection-status-success': connected && ! testConnectionFailed,
						'rank-math-connection-status-error': ! connected || testConnectionFailed,
					} )

					const status = connected ? __( 'Connected', 'rank-math' ) : __( 'Not Connected', 'rank-math' )
					return (
						<div key={ id } className={ wrapperClass } tabIndex={ 0 }>
							<header>
								<h3>
									<span className="rank-math-connection-status-wrap">
										{
											isTestingConnection
												? <svg className='rank-math-spinner' viewBox='0 0 100 100' width='16' height='16' xmlns='http://www.w3.org/2000/svg' role='presentation' focusable='false'><circle cx='50' cy='50' r='50' vector-effect='non-scaling-stroke' /><path d='m 50 0 a 50 50 0 0 1 50 50' vector-effect='non-scaling-stroke' /></svg>
												: <span className={ statusClasses } title={ testConnectionFailed ? __( 'Some permissions are missing, please reconnect', 'rank-math' ) : status } />

										}
									</span>
									{ title }
								</h3>
							</header>
							<div className={ `rank-math-accordion-content rank-math-${ id }-content` }>
								{ disabled && <PermissionsWarning reconnectUrl={ data.reconnectGoogleUrl } /> }
								<View data={ data } updateData={ updateData } />
							</div>
						</div>
					)
				} )
			}
		</>
	)
}
