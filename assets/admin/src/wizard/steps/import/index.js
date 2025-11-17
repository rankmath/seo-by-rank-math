// eslint-disable-next-line eslint-comments/disable-enable-pair
/* eslint-disable no-alert */
/**
 * External dependencies
 */
import { map, isEmpty, reduce, entries, keys } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Plugin from './Plugin'
import ImportProgress from './ImportProgress'
import { Button } from '@rank-math/components'
import getPluginOptions from './helpers/getPluginOptions'

export default ( { data, skipStep } ) => {
	const importablePluginsValue = reduce(
		entries( data.importablePlugins ),
		( acc, [ plugin, options ] ) => {
			if ( ! options.checked ) {
				return acc
			}

			acc[ plugin ] = [ ...( keys( options?.choices ) || [] ), 'recalculate' ]

			return acc
		},
		{}
	)

	const [ selectedPlugins, setSelectedPlugins ] = useState( importablePluginsValue )
	const [ startImport, setStartImport ] = useState( false )
	const [ importComplete, setImportComplete ] = useState( false )

	const handleStartPluginImport = () => {
		if ( importComplete ) {
			skipStep()
			return
		}

		if ( rankMath.isConfigured && ! window.confirm( rankMath.confirm ) ) {
			return false
		}

		if ( isEmpty( selectedPlugins ) ) {
			window.alert( __( 'Please select plugin to import data.', 'rank-math' ) )
			return false
		}

		setStartImport( true )
	}

	return (
		<>
			<div className="import-plugin">
				<div className="field-th">
					<h3>{ __( 'Input Data From:', 'rank-math' ) }</h3>
				</div>

				<div className="field-td">
					{ map( getPluginOptions( data ), ( options, index ) => (
						<Plugin
							key={ index }
							options={ options }
							selectedPlugins={ selectedPlugins }
							setSelectedPlugins={ setSelectedPlugins }
						/>
					) ) }

					{ startImport && (
						<ImportProgress
							selectedPlugins={ selectedPlugins }
							setImportComplete={ setImportComplete }
						/>
					) }
				</div>
			</div>

			<footer className="form-footer wp-core-ui rank-math-ui">
				{ ! importComplete && (
					<Button
						variant="secondary"
						className="button-deactivate-plugins"
						data-deactivate-message={ __( 'Deactivating Plugins…', 'rank-math' ) }
						onClick={ skipStep }
					>
						{ __( "Skip, Don't Import Now", 'rank-math' ) }
					</Button>
				) }

				<Button
					variant="primary"
					className="button-import"
					onClick={ handleStartPluginImport }
					disabled={ startImport && ! importComplete }
				>
					{ importComplete
						? __( 'Continue', 'rank-math' )
						: __( 'Start Import', 'rank-math' ) }
				</Button>
			</footer>
		</>
	)
}
