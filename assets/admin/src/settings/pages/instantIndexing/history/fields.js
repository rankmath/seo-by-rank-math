/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { forEach, isEmpty, map } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal Dependencies
 */
import { Table, Button } from '@rank-math/components'
import addNotice from '@helpers/addNotice'

const IndexNowHistory = () => {
	const [ showHelp, setShowHelp ] = useState( false )
	const [ log, setLog ] = useState( [] )
	const [ filter, setFilter ] = useState( 'all' )

	useEffect( () => {
		apiFetch( {
			method: 'POST',
			path: '/rankmath/v1/in/getLog',
			data: {
				filter: 'all',
			},
		} )
			.catch( ( error ) => {
				alert( error.message )
			} )
			.then( ( response ) => {
				setLog( response.data )
			} )
	}, [] )

	const historyData = [
		[
			__( 'Time', 'rank-math' ),
			__( 'URL', 'rank-math' ),
			__( 'Response', 'rank-math' ),
		],
	]

	if ( ! isEmpty( log ) ) {
		forEach( log, ( data ) => {
			if (
				filter !== 'all' &&
				(
					( filter === 'manual' && ! data.manual_submission ) ||
					( filter === 'auto' && data.manual_submission )
				)
			) {
				return
			}
			historyData.push(
				[
					data.timeHumanReadable,
					data.url,
					data.status.toString(),
				]
			)
		} )
	}

	if ( historyData.length < 2 ) {
		historyData.push(
			[ __( 'No submissions yet.', 'rank-math' ) ]
		)
	}

	const filters = {
		all: __( 'All', 'rank-math' ),
		manual: __( 'Manual', 'rank-math' ),
		auto: __( 'Auto', 'rank-math' ),
	}

	return (
		<>
			{
				! isEmpty( log ) &&
				<div className="indexnow-history-filter-wrapper">
					<div className="history-filter-links" id="indexnow_history_filters">
						{
							map( filters, ( name, key ) => {
								const classes = key === filter ? 'button current' : 'button'
								return (
									<Button
										variant="link"
										className={ classes }
										onClick={ () => ( setFilter( key ) ) }
										key={ key }
									>
										{ name }
									</Button>
								)
							} )
						}
					</div>
					<Button
						variant="tertiary"
						className="{ classes }"
						onClick={ () => {
							apiFetch( {
								method: 'POST',
								path: '/rankmath/v1/in/clearLog',
								data: {
									filter: 'all',
								},
							} )
								.catch( () => {
									addNotice(
										__( 'Error: could not clear history.', 'rank-math' ),
										'error',
										jQuery( '.rank-math-header' ),
									)
								} )
								.then( () => {
									setLog( [] )
								} )
						} }
					>
						{ __( 'Clear History', 'rank-math' ) }
					</Button>
				</div>
			}
			<Table
				id="indexnow_history"
				fields={ historyData }
			/>

			<Button
				variant="link"
				iconPosition="right"
				icon={
					showHelp
						? 'dashicons dashicons-arrow-down'
						: 'dashicons dashicons-arrow-up'
				}
				onClick={ () => setShowHelp( ! showHelp ) }
			>
				{ __( 'Response Code Help', 'rank-math' ) }
			</Button>

			{ showHelp && (
				<Table
					id="indexnow_response_codes"
					fields={
						[
							[
								__( 'Response Code', 'rank-math' ),
								__( 'Response Message', 'rank-math' ),
								__( 'Reasons', 'rank-math' ),
							],
							[
								'200',
								__( 'OK', 'rank-math' ),
								__( 'The URL was successfully submitted to the IndexNow API.', 'rank-math' ),
							],
							[
								'202',
								__( 'Accepted', 'rank-math' ),
								__( 'The URL was successfully submitted to the IndexNow API, but the API key will be checked later.', 'rank-math' ),
							],
							[
								'400',
								__( 'Bad Request', 'rank-math' ),
								__( 'The request was invalid.', 'rank-math' ),
							],
							[
								'403',
								__( 'Forbidden', 'rank-math' ),
								__( 'The key was invalid (e.g. key not found, file found but key not in the file).', 'rank-math' ),
							],
							[
								'422',
								__( 'Unprocessable Entity', 'rank-math' ),
								__( 'The URLs don\'t belong to the host or the key is not matching the schema in the protocol.', 'rank-math' ),
							],
							[
								'429',
								__( 'Too Many Requests', 'rank-math' ),
								__( 'Too Many Requests (potential Spam).', 'rank-math' ),
							],
						]
					} />
			) }
		</>
	)
}

export default [
	{
		id: 'indexnow_history',
		type: 'component',
		Component: IndexNowHistory,
	},
]
