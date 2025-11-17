/**
 * External dependencies
 */
import { get, set, map, cloneDeep } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useSelect, useDispatch } from '@wordpress/data'
import { useEffect, useState, useRef } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { CheckboxControl, TextControl, SelectControl } from '@rank-math/components'

const fieldDefinitions = [
	{
		key: 'max-snippet',
		label: __( 'Snippet', 'rank-math' ),
		type: 'number',
		default: '-1',
		tooltip: __( 'Specify a maximum text-length, in characters, of a snippet for your page.', 'rank-math' ),
	},
	{
		key: 'max-video-preview',
		label: __( 'Video Preview', 'rank-math' ),
		type: 'number',
		default: '-1',
		tooltip: __( 'Specify a maximum duration in seconds of an animated video preview.', 'rank-math' ),
	},
	{
		key: 'max-image-preview',
		label: __( 'Image Preview', 'rank-math' ),
		type: 'select',
		default: 'large',
		options: {
			standard: __( 'Standard', 'rank-math' ),
			large: __( 'Large', 'rank-math' ),
			none: __( 'None', 'rank-math' ),
		},
		tooltip: __( 'Specify a maximum size of image preview to be shown for images on this page.', 'rank-math' ),
	},
]

export default ( { id } ) => {
	const fullData = useSelect( ( select ) => select( 'rank-math-settings' ).getData(), [] )
	const updateData = useDispatch( 'rank-math-settings' ).updateData

	const [ localState, setLocalState ] = useState( {} )
	const hasInitializedRef = useRef( false )

	// Reset ref if ID changes
	useEffect( () => {
		hasInitializedRef.current = false
	}, [ id ] )

	useEffect( () => {
		if ( hasInitializedRef.current || ! id ) {
			return
		}

		const groupData = get( fullData, id, {} )

		const initial = {}
		map( fieldDefinitions, ( { key, default: defaultVal } ) => {
			const val = get( groupData, key )
			if ( val === undefined || val === null ) {
				initial[ key ] = { enabled: true, value: defaultVal }
			} else if ( val === false ) {
				initial[ key ] = { enabled: false, value: defaultVal }
			} else {
				initial[ key ] = { enabled: true, value: val }
			}
		} )

		setLocalState( initial )
		hasInitializedRef.current = true
	}, [ id, fullData ] )

	const updateField = ( changedKey, changedVal ) => {
		const updatedState = cloneDeep( localState )
		set( updatedState, [ changedKey, 'value' ], changedVal )

		// Apply logic to update enabled state (if needed)
		if ( changedVal === false ) {
			set( updatedState, [ changedKey, 'enabled' ], false )
		} else {
			set( updatedState, [ changedKey, 'enabled' ], true )
		}

		// Prepare final group object to save
		const finalGroupData = {}
		map( fieldDefinitions, ( { key } ) => {
			const field = updatedState[ key ] || {}
			finalGroupData[ key ] = field.enabled ? field.value : false
		} )

		const updatedData = cloneDeep( fullData )
		set( updatedData, id, finalGroupData )

		// Dispatch
		updateData( updatedData )
	}

	const handleCheckboxChange = ( key, defaultVal ) => ( isChecked ) => {
		const updated = cloneDeep( localState )
		set( updated, [ key, 'enabled' ], isChecked )

		if ( isChecked ) {
			set( updated, [ key, 'value' ], defaultVal )
		}

		setLocalState( updated )
		updateField( key, isChecked ? defaultVal : false )
	}

	const handleValueChange = ( key ) => ( val ) => {
		const updated = cloneDeep( localState )
		set( updated, [ key, 'value' ], val )
		setLocalState( updated )

		if ( updated[ key ].enabled ) {
			updateField( key, val )
		}
	}

	return (
		<>
			{ map( fieldDefinitions, ( { key, label, tooltip, type, options, default: defaultVal } ) => {
				const enabled = get( localState, [ key, 'enabled' ], false )
				const value = get( localState, [ key, 'value' ], defaultVal )

				return (
					<div className="rank-math-group-field" key={ key }>
						<CheckboxControl
							label={
								<span>
									{ label }
									<span className="rank-math-tooltip">
										<em className="dashicons-before dashicons-editor-help"></em>
										<span>{ tooltip }</span>
									</span>
								</span>
							}
							checked={ enabled }
							onChange={ handleCheckboxChange( key, defaultVal ) }
						/>

						{ type === 'select' ? (
							<SelectControl
								options={ options }
								value={ value }
								disabled={ ! enabled }
								onChange={ handleValueChange( key ) }
							/>
						) : (
							<TextControl
								type="number"
								min={ -1 }
								value={ value }
								disabled={ ! enabled }
								onChange={ handleValueChange( key ) }
							/>
						) }
					</div>
				)
			} ) }
		</>
	)
}
