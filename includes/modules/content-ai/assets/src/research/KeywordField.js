/* global alert */

/**
 * External dependencies
 */
import $ from 'jquery'
import { isEmpty, isNull, isUndefined, isNumber, includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { doAction, applyFilters } from '@wordpress/hooks'
import { Button, TextControl, SelectControl } from '@wordpress/components'
import { useState } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

const researchKeyword = ( data, setLoading, updateData ) => {
	data.objectID = rankMath.objectID
	setLoading( true )
	apiFetch( {
		method: 'POST',
		path: '/rankmath/v1/ca/researchKeyword',
		data,
	} )
		.catch( ( error ) => {
			setLoading( false )
			alert( error.message )
		} )
		.then( ( response ) => {
			setLoading( false )
			updateData( 'researchedData', response.data )
			if ( ! isNull( response.credits ) && ! isUndefined( response.credits ) ) {
				updateData( 'credits', ! isNumber( response.credits ) ? 0 : response.credits )
			}

			updateData( 'keyword', data.keyword )
			updateData( 'country', data.country )

			doAction( 'rank_math_content_ai_changed', response.keyword )
		} )
}

export default ( { data, updateData, showError, isFree, hasCredits, loading, setLoading } ) => {
	const [ keyword, setKeyword ] = useState( data.keyword )
	const [ country, setCountry ] = useState( data.country )

	const forceUpdate = keyword === data.keyword && country === data.country
	return (
		<>
			<div className="rank-math-ca-top-section">
				{
					includes( [ 'elementor', 'divi' ], rankMath.currentEditor ) &&
					<Button onClick={ () => ( $( '.rank-math-general-tab' ).trigger( 'click' ) ) }>
						<i className="dashicons dashicons-arrow-left-alt"></i>
						{ __( 'Back', 'rank-math' ) }
					</Button>
				}
				<SelectControl
					value={ country }
					onChange={ ( newCountry ) => setCountry( newCountry ) }
					options={ data.countries }
					disabled={ ! hasCredits || isFree }
				/>
			</div>

			<div className="rank-math-ca-keywords-wrapper">
				<div className="rank-math-ca-credits-wrapper">
					<TextControl
						label={ __( 'Focus Keyword', 'rank-math' ) }
						value={ keyword }
						disabled={ ! hasCredits || isFree }
						onChange={ ( newKeyword ) => setKeyword( newKeyword ) }
						onKeyDown={ ( e ) => {
							if ( 'Enter' === e.key ) {
								e.preventDefault()
							}
						} }
						help={
							applyFilters(
								'rank_math_content_ai_help_text',
								(
									<>
										{ __( 'Upgrade to buy more credits from ', 'rank-math' ) }
										<a href={ getLink( 'content-ai-pricing-tables', 'Sidebar Upgrade Text' ) } rel="noreferrer" target="_blank" title={ __( 'Content AI Pricing.', 'rank-math' ) }>{ __( 'here.', 'rank-math' ) }</a>
									</>
								)
							)
						}
						placeholder={ __( 'Suggested length 2-3 Words', 'rank-math' ) }
					/>

					<div className="help-text">
						{ __( 'To learn how to use it', 'rank-math' ) } <a href={ getLink( 'content-ai-settings', 'Content AI Sidebar KB Link' ) } target="_blank" rel="noreferrer">{ __( 'Click here', 'rank-math' ) }</a>
					</div>

					{
						forceUpdate && ! loading && ! showError && hasCredits && ! isFree && ! isEmpty( data.researchedData ) &&
						<Button
							className="rank-math-ca-force-update"
							onClick={ () => researchKeyword( { keyword, country, forceUpdate }, setLoading, updateData ) }
							label={ __( 'Refresh will use 500 Credit.', 'rank-math' ) }
							showTooltip={ true }
						>
							<i className="dashicons dashicons-image-rotate"></i>
						</Button>
					}

					{
						! forceUpdate && ! loading && ! showError && hasCredits && ! isFree &&
						<Button
							className="is-primary"
							onClick={ () => researchKeyword( { keyword, country, forceUpdate }, setLoading, updateData ) }
							label={ __( '500 credits will be used.', 'rank-math' ) }
							disabled={ ! keyword }
							showTooltip={ true }
						>
							{ __( 'Research', 'rank-math' ) }
						</Button>
					}

				</div>
			</div>
		</>
	)
}
