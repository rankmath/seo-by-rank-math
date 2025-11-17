/* global confirm */
/**
 * External dependencies
 */
import { isEmpty, forEach, find } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { useState, useEffect } from '@wordpress/element'

/**
 * Internal dependencies
 */
import {
	ToggleControl,
	SelectControl,
	SelectWithSearch,
	ProBadge,
} from '@rank-math/components'
import getLink from '@helpers/getLink'
import ajax from '@helpers/ajax'

const createDataStream = ( selectedProperty, setDataStreams ) => {
	ajax(
		'get_ga4_data_streams',
		{
			propertyID: selectedProperty,
		},
		'post'
	).done( ( response ) => {
		if ( response.error ) {
			console.error( response.error )
			return
		}

		setDataStreams( response.streams )
	} )
}

export default ( { data, updateData } ) => {
	const { analyticsData, allServices } = data
	const accountID = analyticsData.account_id
	const propertyID = analyticsData.property_id ?? ''
	const viewID = analyticsData.view_id ?? ''
	const streamName = analyticsData.stream_name ?? ''
	const isPro = rankMath.isPro

	const accountOptions = {}
	const propertyOptions = new Map()
	propertyOptions.set( '', __( 'Select Property', 'rank-math' ) )
	const streamOptions = {}
	const [ dataStreams, setDataStreams ] = useState( {} )

	if ( ! isEmpty( allServices.accounts ) ) {
		forEach( allServices.accounts, ( account, id ) => {
			accountOptions[ id ] = account.name + ' (' + id + ')'

			if ( accountID === id ) {
				propertyOptions.set( 'create-ga4-property', __( 'Create new GA4 Property', 'rank-math' ) )
				forEach( account.properties, ( property, propID ) => {
					propertyOptions.set( propID, property.name )
				} )
			}

			if ( ! accountID ) {
				analyticsData.account_id = id
				updateData( 'analyticsData', analyticsData )
			}
		} )
	}

	useEffect( () => {
		if ( ! isEmpty( dataStreams ) ) {
			const selectedStream = dataStreams[ 0 ]
			analyticsData.view_id = selectedStream.id
			analyticsData.stream_name = selectedStream.name
			analyticsData.measurement_id = selectedStream.measurementId
			updateData( 'analyticsData', analyticsData )
		}
	}, [ dataStreams ] )

	if ( ! isEmpty( dataStreams ) ) {
		forEach( dataStreams, ( dataStream ) => {
			streamOptions[ dataStream.id ] = dataStream.name
		} )
	}

	if ( viewID && streamName && ! streamOptions[ viewID ] ) {
		streamOptions[ viewID ] = streamName
	}

	if ( ! streamOptions[ viewID ] ) {
		streamOptions[ viewID ] = __( 'Select', 'rank-math' )
	}

	return (
		<>
			<div className="field-row field-type-select">
				<div className="field-row-col">
					<SelectWithSearch
						value={ accountID }
						options={ accountOptions }
						onChange={ ( isChecked ) => {
							analyticsData.account_id = isChecked
							updateData( 'analyticsData', analyticsData )
						} }
						label={ __( 'Account', 'rank-math' ) }
						className="site-analytics-account notrack"
						disabled={ rankMath.isAnalyticsConnected }
					/>
				</div>

				<div className="field-row-col">
					<SelectControl
						value={ propertyID }
						options={ propertyOptions }
						onChange={ ( selectedProperty ) => {
							if ( selectedProperty === 'create-ga4-property' ) {
								// eslint-disable-next-line no-alert
								if ( confirm( __( 'Are you sure, you want to create a new GA4 Property?', 'rank-math' ) ) ) {
									ajax(
										'create_ga4_property',
										{
											accountID,
										},
										'post'
									).done( ( response ) => {
										if ( response.error ) {
											alert( response.error )
											return
										}

										propertyOptions.set( response.id, response.name )

										analyticsData.property_id = response.id
										updateData( 'analyticsData', analyticsData )
										allServices.accounts[ accountID ].properties[ response.id ] = {
											name: response.name,
											id: response.id,
											account_id: accountID,
											type: 'GA4',
										}
										updateData( 'allServices', allServices )
										createDataStream( response.id, setDataStreams )
									} )
								}

								return
							}

							analyticsData.property_id = selectedProperty
							updateData( 'analyticsData', analyticsData )

							// Create Data Stream
							if ( selectedProperty ) {
								createDataStream( selectedProperty, setDataStreams )
							}
						} }
						label={ __( 'Property', 'rank-math' ) }
						className="site-analytics-property notrack"
						disabled={ rankMath.isAnalyticsConnected }
					/>
				</div>

				<div className="field-row-col">
					<SelectControl
						value={ viewID }
						options={ streamOptions }
						onChange={ ( selectedStream ) => {
							analyticsData.view_id = selectedStream
							const stream = find( dataStreams, { id: selectedStream } )
							analyticsData.stream_name = stream.name
							analyticsData.measurement_id = stream.measurementId

							updateData( 'analyticsData', analyticsData )
						} }
						label={ __( 'Data Stream', 'rank-math' ) }
						className="site-analytics-view notrack"
						disabled={ rankMath.isAnalyticsConnected }
					/>
				</div>

				{ applyFilters( 'rank_math_analytics_options_analytics', '', data, updateData ) }
			</div>

			<div className="field-row field-type-toggle">
				<div className="field-td">
					<ToggleControl
						checked={ analyticsData.install_code }
						onChange={ ( isChecked ) => {
							analyticsData.install_code = isChecked
							updateData( 'analyticsData', analyticsData )
						} }
						className="regular-text notrack"
						label={ __( 'Install analytics code', 'rank-math' ) }
					/>

					<div className="field-description">
						{ __(
							'Enable this option only if you are not using any other plugin/theme to install Google Analytics code.',
							'rank-math'
						) }
					</div>
				</div>
			</div>

			{
				analyticsData.install_code &&
				<>
					<div
						data-url={ ! isPro ? getLink( 'free-vs-pro', 'Anonymize IP' ) : undefined }
						className={ `field-row field-type-toggle ${
							! isPro ? 'field-redirector-element' : ''
						}` }
					>
						<div className="field-td">
							<ToggleControl
								disabled={ ! isPro }
								checked={ analyticsData.anonymize_ip }
								onChange={ ( isChecked ) => {
									analyticsData.anonymize_ip = isChecked
									updateData( 'analyticsData', analyticsData )
								} }
								className="regular-text notrack"
								label={
									<>
										{ __( 'Anonymize IP addresses', 'rank-math' ) }
										{ ! isPro && <ProBadge href={ getLink( 'pro', 'Anonymize IP' ) } /> }
									</>
								}
							/>
						</div>
					</div>

					<div
						data-url={ ! isPro ? getLink( 'pro', 'Localjs IP' ) : undefined }
						className={ `field-row field-type-toggle ${
							! isPro ? 'field-redirector-element' : ''
						}` }
					>
						<div className="field-td">
							<ToggleControl
								disabled={ ! isPro }
								checked={ analyticsData.local_ga_js }
								onChange={ ( isChecked ) => {
									analyticsData.local_ga_js = isChecked
									updateData( 'analyticsData', analyticsData )
								} }
								className="regular-text notrack"
								label={
									<>
										{ __( 'Self-Hosted Analytics JS File', 'rank-math' ) }
										{ ! isPro && <ProBadge href={ getLink( 'pro', 'Localjs IP' ) } /> }
									</>
								}
							/>
						</div>
					</div>

					<div className="field-row field-type-toggle">
						<div className="field-td">
							<ToggleControl
								checked={ analyticsData.exclude_loggedin }
								onChange={ ( isChecked ) => {
									analyticsData.exclude_loggedin = isChecked
									updateData( 'analyticsData', analyticsData )
								} }
								className="regular-text notrack"
								label={ __( 'Exclude Logged-in users', 'rank-math' ) }
							/>
						</div>
					</div>
				</>
			}
		</>
	)
}
