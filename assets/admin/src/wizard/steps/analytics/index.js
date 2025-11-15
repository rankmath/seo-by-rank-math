/**
 * External dependencies
 */
import { isEmpty, update } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect } from '@wordpress/element'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import ConnectGoogle from './components/ConnectGoogle'
import ConnectRankMath from './components/ConnectRankMath'
import Sections from './sections'
import EmailReports from './sections/EmailReports'
import { ToggleControl, Button, TextControl, PrivacyBox } from '@rank-math/components'
import ajax from '@helpers/ajax'

export default ( { data, updateData } ) => {

	const [ state, setState ] = useState( {
		days: rankMath?.dbInfo?.days,
		rows: rankMath?.dbInfo?.rows,
		size: rankMath?.dbInfo?.size,
		isDeleting: false,
		isUpdating: false,
		isCanceling: false,
		updateBtnText: rankMath.isFetching
			? __( 'Fetching in Progress', 'rank-math' )
			: __( 'Update data manually', 'rank-math' ),
		isFetching: rankMath.isFetching || false,
	} )

	const {
		isDeleting,
		days,
		rows,
		size,
		isUpdating,
		isCanceling,
		updateBtnText,
		isFetching,
	} = state

	const {
		isSiteConnected,
		isAuthorized,
		searchConsole,
		allServices,
		console_caching_control,
		analytics_stats,
	} = data
	if ( ! isSiteConnected ) {
		return <ConnectRankMath { ...data } />
	}

	if ( ! isAuthorized ) {
		return <ConnectGoogle { ...data } />
	}

	const accounts = allServices?.accounts || []

	useEffect( () => {
		if ( ! isEmpty( accounts ) ) {
			return
		}

		ajax( 'google_check_all_services' ).done( ( response ) => {
			searchConsole.sites = response.sites
			if ( ! response.inSearchConsole ) {
				ajax( 'add_site_console' ).done( ( siteConsoleresponse ) => {
					searchConsole.sites = siteConsoleresponse.sites
				} )
			} else if ( ! response.isVerified ) {
				ajax( 'verify_site_console' )
			}
			searchConsole.profile = response.sites[ response.homeUrl ]
			updateData( 'searchConsole', searchConsole )

			if ( ! isEmpty( response.adsenseAccounts ) ) {
				allServices.adsenseAccounts = response.adsenseAccounts || {}
				updateData( 'allServices', allServices )
			}

			if ( ! isEmpty( response.accounts ) ) {
				allServices.accounts = response.accounts
				updateData( 'allServices', allServices )
			}
		} )
	}, [] )

	const doDelete = () => {
		const days = console_caching_control || 90
		const message =
				-1 === days
					? rankMath.confirmClearImportedData
					: rankMath.confirmClear90DaysCache

		if ( ! window.confirm( message + ' ' + rankMath.confirmAction ) ) {
			return
		}

		if ( isDeleting ) {
			return
		}

		setState( ( prevState ) => ( {
			...prevState,
			isDeleting: true,
		} ) )

		ajax( 'analytics_delete_cache', { days }, 'GET' )
			.done( function( result ) {
				if ( result && result.success ) {
					setState( ( prevState ) => ( {
						...prevState,
						isDeleting: false,
						days: result?.days,
						rows: result?.rows,
						size: result?.size,
					} ) )
				}
			} )
	}

	const doUpdate = () => {
		if ( isUpdating ) {
			return
		}

		const days = console_caching_control || 90

		setState( ( prevState ) => ( {
			...prevState,
			isUpdating: true,
			updateBtnText: __( 'Starting update…', 'rank-math' ),
		} ) )

		ajax( 'analytic_start_fetching', { days }, 'GET' ).done(
			function( result ) {
				if ( result && result.success ) {
					setState( ( prevState ) => ( {
						...prevState,
						isUpdating: false,
						updateBtnText: __( 'Fetching in Progress', 'rank-math' ),
						isFetching: true,
					} ) )
					return
				}

				setTimeout( () => {
					setState( ( prevState ) => ( {
						...prevState,
						updateBtnText: __( 'Update data manually', 'rank-math' ),
					} ) )
				}, 2000 )
			}
		)
	}

	const doCancelFetch = () => {
		if ( isCanceling ) {
			return
		}

		setState( ( prevState ) => ( {
			...prevState,
			isCanceling: true,
		} ) )

		ajax( 'analytic_cancel_fetching', {}, 'GET' )
			.done( function( result ) {
				setState( ( prevState ) => ( {
					...prevState,
					isCanceling: false,
				} ) )

				if ( result && result.success ) {
					window.location.reload()
					return
				}
			} )
	}

	return (
		<div className="field-wrap form-table wp-core-ui rank-math-ui">
			<div
				id="field-metabox-rank-math-wizard"
				className="field-metabox field-list"
			>

				<Sections data={ data } updateData={ updateData } />

				<PrivacyBox className="width-100" />

				{
					rankMath.isSettingsPage &&
					<>
						<div className="field-row rank-math-advanced-option field-id-console_caching_control field-type-text">
							<div className="field-th">
								<label htmlFor="console_caching_control">{ __( 'Analytics Database', 'rank-math' ) }</label>
							</div>
							<div className="field-td">
								<TextControl
									type="number"
									autoCorrect="off"
									autoComplete="off"
									autoCapitalize="none"
									variant="regular-text"
									spellCheck={ false }
									value={ console_caching_control }
									onBlur={ ( e ) => {
										const newValue = e?.target?.value?.trim() || ''
										let days = parseInt( newValue )
										if ( isNaN( days ) ) {
											return
										}

										updateData( 'console_caching_control', days )
									} }
									onChange={ ( newValue ) => {
										updateData( 'console_caching_control', newValue )
									} }
									placeholder={ __( 'Enter number of days. E.g. 90', 'rank-math' ) }
								/>
								<div className="field-description" dangerouslySetInnerHTML={ { __html: rankMath.fields.console_caching_control.description } } />

								<br />
								<Button
									className="button-small"
									onClick={ doDelete }
									disabled={ isDeleting }
								>
									{
										isDeleting
											? __( 'Deleting data…', 'rank-math' )
											: __( 'Delete data', 'rank-math' )
									}
								</Button>
								&nbsp;&nbsp;
								<Button
									className="button-small"
									onClick={ doUpdate }
									disabled={ isUpdating || isFetching }
								>
									{ updateBtnText }
								</Button>
								&nbsp;&nbsp;
								<Button
									className="button-small is-destructive"
									onClick={ doCancelFetch }
									disabled={ ! isFetching || isCanceling }
								>
									{ __( 'Cancel Fetch', 'rank-math' ) }
								</Button>

								<br />
								<div className="rank-math-console-db-info">
									<span className="dashicons dashicons-calendar-alt"></span> Cached Days: <strong>{ days }</strong>
								</div>
								<div className="rank-math-console-db-info">
									<span className="dashicons dashicons-editor-ul"></span> Data Rows: <strong>{ rows }</strong>
								</div>
								<div className="rank-math-console-db-info">
									<span className="dashicons dashicons-editor-code"></span> Size: <strong>{ size }</strong>
								</div>
							</div>
						</div>

						{ applyFilters( 'rank_math_settings_general_analytics_stats', '', data, updateData ) }

						<div className="field-row field-type-toggle field-id-analytics_stats">
							<div className="field-th">
								<label htmlFor="analytics_stats">
									{ __( 'Frontend Stats Bar', 'rank-math' ) }
								</label>
							</div>
							<div className="field-td">
								<ToggleControl
									id="analytics_stats"
									checked={ analytics_stats }
									onChange={ ( isChecked ) => {
										updateData( 'analytics_stats', isChecked )
									} }
								/>
								<div className="field-description">
									{ __( 'Enable this option to show Analytics Stats on the front just after the admin bar.', 'rank-math' ) }
								</div>
							</div>
						</div>
					</>
				}

				<EmailReports data={ data } updateData={ updateData } />
			</div>
		</div>
	)
}
