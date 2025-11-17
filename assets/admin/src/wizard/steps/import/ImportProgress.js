/**
 * External Dependencies
 */
import {
	entries,
	flatMap,
	floor,
	forEach,
	keys,
	map,
} from 'lodash'

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, useEffect, useState } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import { TextareaControl } from '@rank-math/components'
import { Analyzer, Paper, ResultManager } from '@rankMath/analyzer'
import getResearchesTests from './helpers/getResearchesTests'

export default ( { selectedPlugins, setImportComplete } ) => {
	const [ logText, setLogText ] = useState( '' )
	const [ progress, setProgress ] = useState( 1 )

	const importData = { ...selectedPlugins }
	const pluginsList = entries( selectedPlugins )
	const pluginNames = map( pluginsList, ( plugin ) => plugin[ 0 ] )
	const totalActions = flatMap( map( pluginsList, ( plugin ) => plugin[ 1 ] ) ).length
	const postIds = []

	const addLog = ( msg ) => {
		const currentdate = new Date()
		const text =
			'[' +
			( 10 > currentdate.getHours() ? '0' : '' ) +
			currentdate.getHours() +
			':' +
			( 10 > currentdate.getMinutes() ? '0' : '' ) +
			currentdate.getMinutes() +
			':' +
			( 10 > currentdate.getSeconds() ? '0' : '' ) +
			currentdate.getSeconds() +
			'] ' +
			msg +
			'\n'

		setLogText( ( prevLog ) => prevLog + text )
	}

	const updateSeoScores = ( postsData, slug, actions, paged, callback ) => {
		const postScores = {}

		if ( postsData === 'complete' ) {
			ajaxImport( slug, actions, paged, callback )
			return
		}

		return new Promise( ( resolve ) => {
			forEach( entries( postsData ), ( [ postID, data ] ) => {
				if ( postIds.includes( postID ) ) {
					return
				}

				postIds.push( postID )
				const resultManager = new ResultManager()
				const i18n = wp.i18n
				const paper = new Paper()
				paper.setTitle( data.title )
				paper.setDescription( data.description )
				paper.setText( data.content )
				paper.setKeyword( data.keyword )
				paper.setKeywords( data.keywords )
				paper.setPermalink( data.url )
				paper.setUrl( data.url )

				if ( data.thumbnail ) {
					paper.setThumbnail( data.thumbnail )
				}

				paper.setContentAI( data.hasContentAi )

				const researches = getResearchesTests( data )
				const analyzer = new Analyzer( { i18n, analysis: researches } )

				analyzer.analyzeSome( researches, paper ).then( ( results ) => {
					resultManager.update( paper.getKeyword(), results, true )

					let score = resultManager.getScore( data.keyword )
					if ( data.isProduct ) {
						score += data.isReviewEnabled ? 1 : 0
						score += data.hasProductSchema ? 1 : 0
					}

					postScores[ postID ] = score
				} )
			} )

			resolve()
		} ).then( () => {
			fetch( rankMath.api.root + 'rankmath/v1/updateSeoScore', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': rankMath.restNonce,
				},
				body: JSON.stringify( {
					action: 'rank_math_update_seo_score',
					postScores,
				} ),
			} )
				.then( ( response ) => {
					if ( ! response.ok ) {
						throw new Error( response.statusText )
					}
					return response.json()
				} )
				.then( () => {
					addLog( 'SEO Scores updated' )
					ajaxImport( slug, actions, paged, callback )
				} )
				.catch( ( error ) => {
					addLog( error.message )
				} )
		} )
	}

	const ajaxImport = ( from, actions, paged, callback, plugin ) => {
		if ( 0 === actions.length ) {
			callback()
			return
		}

		paged = paged || 1
		const action = actions.shift()
		let message =
			'deactivate' === action
				? 'Deactivating ' + plugin
				: 'Importing ' + action + ' from ' + plugin
		let actionProgress = floor( 100 / totalActions )

		if ( 'recalculate' === action ) {
			message = 'Starting SEO score recalculation'
		}

		addLog( message )

		fetch( rankMath.ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams( {
				perform: action,
				pluginSlug: from,
				paged,
				action: 'rank_math_import_plugin',
				security: rankMath.security,
			} ),
		} )
			.then( ( response ) => {
				if ( ! response.ok ) {
					throw new Error( response.statusText )
				}

				return response.json()
			} )
			.then( ( result ) => {
				let currentPage = 1

				if ( result && result.page && result.page < result.total_pages ) {
					currentPage = result.page + 1
					actions.unshift( action )
				}

				if ( result && result.total_pages ) {
					actionProgress = Math.ceil( actionProgress / result.total_pages )
				}

				setProgress( ( prev ) => prev + actionProgress )

				if ( action === 'recalculate' && result.total_items > 0 ) {
					updateSeoScores( result.data, plugin, actions, paged, callback )
				} else {
					if ( action === 'recalculate' && result.total_items === 0 ) {
						result.message = __(
							'No posts found without SEO score.',
							'rank-math'
						)
					}

					addLog( result.success ? result.message : result.error )
					ajaxImport( from, actions, currentPage, callback, plugin )
				}
			} )
			.catch( ( error ) => {
				addLog( error.message )
				ajaxImport( from, actions, null, callback, plugin )
			} )
	}

	const pluginsData = ( callback ) => {
		const pluginKeys = keys( importData )

		const plugin = pluginKeys[ 0 ]
		const actions = importData[ plugin ]

		const from = keys( importData )[ 0 ]

		delete importData[ from ]

		if ( 0 === pluginKeys.length ) {
			addLog(
				'Import finished. Click on the button below to continue the Setup Wizard.'
			)
			callback()
			return
		}

		ajaxImport(
			from,
			actions,
			null,
			function() {
				pluginsData( callback )
			},
			plugin
		)
	}

	useEffect( () => {
		addLog( 'Import started...' )
		pluginsData( () => {
			setProgress( 100 )
		} )
		setImportComplete( true )
	}, [] )

	useEffect( () => {
		const textarea = document.querySelector( '#import-progress-textarea' )
		textarea.scrollTop = textarea.scrollHeight - textarea.clientHeight - 20
	}, [ addLog ] )

	return (
		<>
			<div id="import-progress-bar">
				<div id="importProgress">
					<div id="importBar" style={ { width: progress + '%' } }></div>
				</div>

				<span className="left">
					<strong>{ __( 'Importing: ', 'rank-math' ) }</strong>
					<span className="plugin-from">
						{ map( pluginNames, ( name, index ) => {
							return (
								<Fragment key={ index }>
									{ name }

									{ index < pluginsList.length - 1 && ', ' }
								</Fragment>
							)
						} ) }
					</span>
				</span>

				<span className="right">
					<span className="number">{ progress }</span>
					{ __( '% Completed', 'rank-math' ) }
				</span>
			</div>

			<TextareaControl
				disabled
				rows={ 8 }
				value={ logText }
				id="import-progress-textarea"
			/>
		</>
	)
}
