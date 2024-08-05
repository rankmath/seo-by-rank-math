/**
 * External dependencies
 */
import { tail } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, createElement, useState } from '@wordpress/element'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { PanelBody, Button } from '@wordpress/components'
import apiFetch from '@wordpress/api-fetch'

/*
* Internal dependencies
*/
// import ContentAI from './tabs/ContentAI'
import getLink from '@helpers/getLink'
import Research from './Research'
import Write from './Write'
import AITool from './AITool'
import Chat from './Chat'
import TabPanel from '@components/TabPanel'
// import hasError from '../page/helpers/hasError'

const getTabs = () => {
	let tabs = [
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
			view: Research,
			className: 'rank-math-general-tab',
		},
	]

	if ( rankMath.currentEditor !== 'divi' ) {
		tabs.push(
			{
				name: 'write',
				title: (
					<Fragment>
						<i
							className="rm-icon rm-icon-edit"
							title={ __( 'Write', 'rank-math' ) }
						></i>
						<span>{ __( 'Write', 'rank-math' ) }</span>
					</Fragment>
				),
				view: Write,
				className: 'rank-math-write-tab',
			}
		)
	}

	tabs.push(
		{
			name: 'ai-tools',
			title: (
				<Fragment>
					<i
						className="rm-icon rm-icon-page"
						title={ __( 'AI Tools', 'rank-math' ) }
					></i>
					<span>{ __( 'AI Tools', 'rank-math' ) }</span>
				</Fragment>
			),
			view: AITool,
			className: 'rank-math-ai-tools-tab',
		},
		{
			name: 'chat',
			title: (
				<Fragment>
					<i
						className="rm-icon rm-icon-comments"
						title={ __( 'Chat', 'rank-math' ) }
					></i>
					<span>{ __( 'Chat', 'rank-math' ) }</span>
				</Fragment>
			),
			view: Chat,
			className: 'rank-math-chat-tab',
		},
	)

	// Move research tab at the end on sites having Free Content AI plan.
	if ( rankMath.contentAI.plan === 'free' ) {
		tabs.push( tabs[ 0 ] )
		tabs = tail( tabs )
	}

	return tabs
}

const kFormatter = ( num ) => {
	return Math.abs( num ) > 999
		? ( Math.sign( num ) * ( Math.abs( num ) / 1000 ).toFixed( 1 ) ) + 'k' // if value > 999, format to 1.0k
		: Math.sign( num ) * Math.abs( num ) // if value < 1000, nothing to do
}

const ContentAITab = ( props ) => {
	const [ loading, setLoading ] = useState( '' )
	const { data } = props

	const className = classnames( 'rank-math-tooltip update-credits', {
		loading,
	} )

	return (
		<PanelBody className="rank-math-content-ai-wrapper" initialOpen={ true }>
			{
				rankMath.contentAI.isUserRegistered &&
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
										props.updateData( 'credits', response )
									}
									setLoading( '' )
								} )
						} }
					>
						<i className="dashicons dashicons-image-rotate"></i>
						<span>{ __( 'Click to refresh the available credits.', 'rank-math' ) }</span>
					</Button>
					<span>{ __( 'Credits', 'rank-math' ) }</span> <strong title={ data.credits }>{ kFormatter( data.credits ) }</strong>
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
							{ createElement( tab.view, { ...props } ) }
						</div>
					</>
				) }
			</TabPanel>
		</PanelBody>
	)
}

export default compose(
	withSelect( ( select ) => {
		const data = select( 'rank-math-content-ai' ).getData()
		return {
			data,
			hasError: ! data.isUserRegistered || ! data.plan || ! data.credits || data.isMigrating,
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		return {
			updateData( key, value ) {
				dispatch( 'rank-math-content-ai' ).updateData( key, value )
			},
		}
	} )
)( ContentAITab )
