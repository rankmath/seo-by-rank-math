/* global confirm */
/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { map, keys } from 'lodash'

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { useState } from '@wordpress/element'

/**
 * Internal Dependencies
 */
import { CheckboxList, Button, TextareaControl } from '@rank-math/components'
import addNotice from '@helpers/addNotice'
import ajax from '@helpers/ajax'
import ajaxImport from './ajaxImport'
import addLog from './addLog'

export default ( { slug, choices: importChoices, pluginName, importablePlugins, updateViewData } ) => {
	if ( slug !== 'redirections' ) {
		importChoices.recalculate = __( 'Calculate SEO Scores', 'rank-math' )
	}

	const [ choices, setChoices ] = useState( keys( importChoices ) )
	const [ isImporting, setImporting ] = useState( false )
	const [ logger, setLogger ] = useState( [] )

	const noticeLocation = jQuery( '.wp-header-end' )

	return (
		<div className="rank-math-box-content">
			<CheckboxList
				variant="default"
				value={ choices }
				onChange={ setChoices }
				options={ map( importChoices, ( label, id ) => ( { id, label } ) ) }
			/>

			{
				logger.length !== 0 &&
				<TextareaControl
					disable="true"
					value={ logger.join( '\n' ) }
					className="import-progress-area large-text"
					rows="8"
					style={ { marginRight: '20px', background: '#eee' } }
				/>
			}

			<footer>
				<Button
					variant="primary"
					onClick={ () => {
						if (
							// eslint-disable-next-line no-alert
							! confirm(
								// translators: Importer plugin name
								sprintf( __( 'Are you sure you want to import data from %s?', 'rank-math' ), pluginName )
							)
						) {
							return
						}

						if ( choices.length < 1 ) {
							addNotice(
								__( 'Select data to import.', 'rank-math' ),
								'error',
								noticeLocation,
								5000
							)
							return
						}

						setImporting( true )
						const actions = choices
						actions.push( 'deactivate' )
						addLog( 'Import started...', logger, setLogger )

						ajaxImport(
							slug,
							actions,
							logger,
							setLogger,
							null,
							() => {
								setImporting( true )
								setTimeout( () => {
									setLogger( [] )
								}, 10000 )
							}
						)
					} }
					disabled={ isImporting }
				>
					{ __( 'Import', 'rank-math' ) }
				</Button>

				<Button
					isDestructive
					onClick={ () => {
						if (
							// eslint-disable-next-line no-alert
							! confirm(
								// translators: Importer plugin name
								sprintf( __( 'Are you sure you want erase all traces of %s?', 'rank-math' ), pluginName )
							)
						) {
							return
						}

						ajax( 'clean_plugin', { pluginSlug: slug } )
							.done( ( response ) => {
								if ( response.success ) {
									const plugins = importablePlugins
									delete plugins[ slug ]
									updateViewData( { importablePlugins: plugins } )
								}
								addNotice(
									response.success
										? response.message
										: response.error,
									response.success ? 'success' : 'error',
									noticeLocation,
									5000
								)
							} )
					} }
				>
					{ __( 'Clean', 'rank-math' ) }
				</Button>
			</footer>
		</div>
	)
}
