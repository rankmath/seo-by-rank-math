/* global confirm */

/**
 * External dependencies
 */
import { map, compact, forEach, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import decodeEntities from '@helpers/decodeEntities'
import ajax from '@helpers/ajax'

const testConnection = ( data, testData, setTestConnection ) => {
	const tests = applyFilters(
		'rank_math_test_connections',
		compact(
			[
				data.isConsoleConnected && {
					id: 'search-console',
					action: 'check_console_request',
				},
				data.isAnalyticsConnected && {
					id: 'analytics',
					action: 'check_analytics_request',
				},
			]
		),
	)

	forEach( tests, ( test ) => {
		testData[ test.id ] = 'loading'
		setTestConnection( { ...testData } )
		ajax( test.action, {}, 'post' ).done( ( response ) => {
			if ( response.success ) {
				testData[ test.id ] = 'success'
				setTestConnection( { ...testData } )
				return
			}
			testData[ test.id ] = 'failed'
			setTestConnection( { ...testData } )
		} )
	} )
}

/**
 * Generate actions related to Google connections
 */
const getConnectActions = ( data ) => {
	const { reconnectGoogleUrl, isConsoleConnected, isAdsenseConnected, isAnalyticsConnected } = data
	const mode = data?.setup_mode || 'advanced'
	let actions = [
		{
			id: 'reconnect',
			link: decodeEntities( reconnectGoogleUrl ),
			classes: 'rank-math-reconnect-google',
			text: __( 'Reconnect', 'rank-math' ),
		},
		{
			id: 'disconnect',
			link: '#',
			classes: 'rank-math-disconnect-google',
			text: __( 'Disconnect', 'rank-math' ),
		},
	]

	if ( 'advanced' === mode && ( isConsoleConnected || isAdsenseConnected || isAnalyticsConnected ) ) {
		actions.push( {
			id: 'test_connections',
			link: '#',
			classes: 'rank-math-test-connection-google',
			text: __( 'Test Connections', 'rank-math' ),
		} )
	}

	actions = applyFilters( 'rank_math/analytics/connect_actions', actions )

	return actions
}

export default ( { data, testData, setTestConnection } ) => {
	return (
		<div className="connect-actions">
			{ map( getConnectActions( data ), ( { id, link, text, classes } ) => (
				<a
					key={ text }
					href={ link }
					className={ `button button-link ${ classes }` }
					onClick={ ( e ) => {
						if ( id === 'disconnect' ) {
							e.preventDefault()
							// eslint-disable-next-line no-alert
							if ( confirm( __( 'Are you sure you want to disconnect Google services from your site?', 'rank-math' ) ) ) {
								ajax( 'disconnect_google' ).done( () => {
									window.location.reload()
								} )
							}
							return false
						}

						if ( id === 'test_connections' ) {
							e.preventDefault()
							testConnection( data, testData, setTestConnection )
							return false
						}
					} }
				>
					{ text }
				</a>
			) ) }
		</div>
	)
}
