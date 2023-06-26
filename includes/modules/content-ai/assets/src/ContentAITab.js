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
// import AITool from './page/tabs/ai-tools'
// import Write from './page/tabs/write'
// import Chat from './page/tabs/chat'
import getLink from '@helpers/getLink'

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

	// const tabs = [
	// 	{
	// 		name: 'content-ai',
	// 		title: (
	// 			<Fragment>
	// 				<i
	// 					className="rm-icon rm-icon-analyzer"
	// 					title={ __( 'Research', 'rank-math' ) }
	// 				></i>
	// 				<span>{ __( 'Research', 'rank-math' ) }</span>
	// 			</Fragment>
	// 		),
	// 		view: ContentAI,
	// 		className: 'rank-math-general-tab',
	// 	},
	// ]

	// if ( rankMath.isUserRegistered ) {
	// 	tabs.push(
	// 		{
	// 			name: 'write',
	// 			title: (
	// 				<Fragment>
	// 					<i
	// 						className="rm-icon rm-icon-edit"
	// 						title={ __( 'Write', 'rank-math' ) }
	// 					></i>
	// 					<span>{ __( 'Write', 'rank-math' ) }</span>
	// 				</Fragment>
	// 			),
	// 			view: Write,
	// 			className: 'rank-math-write-tab',
	// 		},
	// 		{
	// 			name: 'ai-tools',
	// 			title: (
	// 				<Fragment>
	// 					<i
	// 						className="rm-icon rm-icon-page"
	// 						title={ __( 'AI Tools', 'rank-math' ) }
	// 					></i>
	// 					<span>{ __( 'AI Tools', 'rank-math' ) }</span>
	// 				</Fragment>
	// 			),
	// 			view: AITool,
	// 			className: 'rank-math-ai-tools-tab',
	// 		},
	// 		{
	// 			name: 'chat',
	// 			title: (
	// 				<Fragment>
	// 					<i
	// 						className="rm-icon rm-icon-comments"
	// 						title={ __( 'Chat', 'rank-math' ) }
	// 					></i>
	// 					<span>{ __( 'Chat', 'rank-math' ) }</span>
	// 				</Fragment>
	// 			),
	// 			view: Chat,
	// 			className: 'rank-math-chat-tab',
	// 		},
	// 	)
	// }
	const className = classnames( 'rank-math-tooltip update-credits', {
		loading,
	} )

	const kFormatter = ( num ) => {
		return Math.abs( num ) > 999
			? Math.sign( num ) * ( Math.abs( num ) / 1000 ).toFixed( 1 ) +'k' // if value > 999, format to 1.0k
			: Math.sign( num ) * Math.abs( num ) // if value < 1000, nothing to do
	}

	return (
		<PanelBody className="rank-math-content-ai-wrapper" initialOpen={ true }>
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
								setCredits( response )
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
			<TabPanel
				className="rank-math-tabs"
				activeClass="is-active"
				tabs={ getTabs() }
			>
				{ ( tab ) => (
					<div className={ 'rank-math-tab-content-' + tab.name }>
						{ createElement( tab.view, { setCredits } ) }
					</div>
				) }
			</TabPanel>
		</PanelBody>
	)
}
