/**
 * External dependencies
 */
import { filter, forEach, includes, keys, map } from 'lodash'

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element'
import { Button, Icon } from '@wordpress/components'
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { CheckboxControl, CheckboxList } from '@rank-math/components'

/**
 * Import plugin component.
 *
 * @param {Object}   props                    Component props.
 * @param {Object}   props.options            The plugin options.
 * @param {Object}   props.selectedPlugins    Object of selected plugin values.
 * @param {Function} props.setSelectedPlugins Callback executed to change selected plugins value.
 */
export default ( { options, setSelectedPlugins, selectedPlugins } ) => {
	const [ showPanel, setShowPanel ] = useState( false )

	const { name, plugin, metaOptions, metaDescription } = options
	const recalculateKey = 'recalculate'
	const value = selectedPlugins[ plugin ]

	const singlePlugins = [
		'yoast',
		'seopress',
		'aioseo',
		'all-in-one-seo-pack-pro',
		'yoast-premium',
		'aio-rich-snippet',
		'wp-schema-pro',
	]

	/**
	 * Remove non-duplicatable plugins
	 *
	 * @param {Object} updatedValue - The updated value object.
	 */
	const removeDuplicatePlugins = ( updatedValue ) => {
		forEach( keys( updatedValue ), ( key ) => {
			if ( includes( singlePlugins, key ) ) {
				delete updatedValue[ key ]
			}
		} )
	}

	/**
	 * Update the selected plugins value.
	 *
	 * @param {Array} newActions
	 */
	const updateSelectedPlugins = ( newActions ) => {
		const updatedValue = { ...selectedPlugins }

		if ( includes( singlePlugins, plugin ) ) {
			removeDuplicatePlugins( updatedValue )
		}

		if ( newActions.length > 0 ) {
			updatedValue[ plugin ] = newActions
		} else {
			delete updatedValue[ plugin ]
		}

		setSelectedPlugins( updatedValue )
	}

	const handlePluginChange = ( isSelected ) => {
		const metaSettings = map( metaOptions, ( option ) => option.id )
		const actions = isSelected ? [ ...metaSettings, recalculateKey ] : []

		return updateSelectedPlugins( actions )
	}

	const handleMetaChange = ( newMetaOptions ) => {
		const currentActions = value

		const actions = [
			...newMetaOptions,
			...( includes( currentActions, recalculateKey ) ? [ recalculateKey ] : [] ),
		]

		return updateSelectedPlugins( actions )
	}

	const handleRecalculateChange = ( isRecalculate ) => {
		const currentActions = value

		const actions = [
			...filter( currentActions, ( action ) => action !== recalculateKey ),
			...( isRecalculate ? [ recalculateKey ] : [] ),
		]

		return updateSelectedPlugins( actions )
	}

	return (
		<>
			<div className={ `plugin-title ${ showPanel ? 'is-open' : '' }` }>
				<CheckboxControl
					variant="metabox"
					checked={ Boolean( value ) }
					onChange={ handlePluginChange }
				/>

				<Button onClick={ () => setShowPanel( ( prev ) => ! prev ) }>
					<h3>{ name }</h3>

					<Icon icon={ showPanel ? 'arrow-up-alt2' : 'arrow-down-alt2' } />
				</Button>
			</div>

			{ showPanel && (
				<div className="inside">
					<CheckboxList
						toggleAll
						variant="metabox"
						value={ value }
						onChange={ handleMetaChange }
						options={ metaOptions }
					/>

					<p
						className="description"
						dangerouslySetInnerHTML={ { __html: metaDescription } }
					/>

					<CheckboxControl
						variant="metabox"
						label={ __( 'Recalculate SEO Scores', 'rank-math' ) }
						checked={ includes( value, recalculateKey ) }
						onChange={ handleRecalculateChange }
					/>
				</div>
			) }
		</>
	)
}
