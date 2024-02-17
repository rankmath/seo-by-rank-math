/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, createElement, useState } from '@wordpress/element'
import { PanelBody, Button } from '@wordpress/components'
import { applyFilters } from '@wordpress/hooks'
import TabPanel from '@components/TabPanel'
import apiFetch from '@wordpress/api-fetch'
import classnames from 'classnames'

/*
* Internal dependencies
*/
import ContentAI from './ContentAI'
import getLink from '@helpers/getLink'
import hasError from '../page/helpers/hasError'

const getTabs = () => {
	return applyFilters(
		'rank_math_content_ai_tabs',
		[
			{
				name: 'content-ai',
				title: (
					<Fragment>
						<i
							className="rm-icon rm-icon-analyzer"
							title={ __( 'Research', 'rank-math' ) }
						></i>
						<span>{ __( 'Research', 'rank-math' ) }</span>
					</Fragment>
				),
				view: ContentAI,
				className: 'rank-math-general-tab',
			},
		]
	)
}

export default function() {
	const [ loading, setLoading ] = useState( '' )
	const [ credits, setCredits ] = useState( rankMath.ca_credits )

	const className = classnames( 'rank-math-tooltip update-credits', {
		loading,
	} )

	const kFormatter = ( num ) => {
		return Math.abs( num ) > 999
			? ( Math.sign( num ) * ( Math.abs( num ) / 1000 ).toFixed( 1 ) ) + 'k' // if value > 999, format to 1.0k
			: Math.sign( num ) * Math.abs( num ) // if value < 1000, nothing to do
	}

	return (
		<PanelBody className="rank-math-content-ai-wrapper" initialOpen={ true }>
			{
				rankMath.isUserRegistered &&
				<div className="rank-math-ca-credits">
					<Button
						className={ className }
						onClick={ () => {
							setLoading( true )
							apiFetch( {
								method: 'POST',
								path: '/rankmath/v1/ca/getCredits',
							} )
								.catch( ( error ) => {
									setLoading( '' )
									alert( error.message )
								} )
								.then( ( response ) => {
									if ( response.error ) {
										alert( response.error )
									} else {
										setCredits( response )
									}
									setLoading( '' )
								} )
						} }
					>
						<i className="dashicons dashicons-image-rotate"></i>
						<span>{ __( 'Click to refresh the available credits.', 'rank-math' ) }</span>
					</Button>
					<span>{ __( 'Credits', 'rank-math' ) }</span> <strong title={ credits }>{ kFormatter( credits ) }</strong>
					<a
						href={ getLink( 'content-ai-credits-usage', 'Sidebar Credits Tooltip Icon' ) }
						rel="noreferrer"
						target="_blank"
						id="rank-math-help-icon"
						title={ __( 'Know more about credits.', 'rank-math' ) }
					>
						&#65110;
					</a>
				</div>
			}
			<TabPanel
				className="rank-math-tabs"
				activeClass="is-active"
				tabs={ getTabs() }
			>
				{ ( tab ) => (
					<>
						<div className={ 'rank-math-tab-content-' + tab.name }>
							{ createElement( tab.view, { setCredits, hasContentAiError: hasError() } ) }
						</div>
					</>
				) }
			</TabPanel>
		</PanelBody>
	)
}
