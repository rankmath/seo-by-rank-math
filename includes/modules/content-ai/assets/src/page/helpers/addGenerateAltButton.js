/**
 * External dependencies
 */
import { isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { createHigherOrderComponent } from '@wordpress/compose'
import { Button } from '@wordpress/components'
import { Fragment, useState } from '@wordpress/element'
import { InspectorControls } from '@wordpress/block-editor'

/**
 * Internal dependencies
 */
import altTextGenerator from '../../components/altTextGenerator'
import hasError from './hasError'
import showCTABox from '@helpers/showCTABox'

export default createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( props.name !== 'core/image' || isEmpty( props.attributes.url ) ) {
			return <BlockEdit { ...props } />
		}

		// State for managing UI feedback
		const [ isGenerating, setIsGenerating ] = useState( false )
		const [ error, setError ] = useState( '' )
		const [ errorClass, setErrorClass ] = useState( '' )

		const { attributes, setAttributes } = props
		const { url, alt } = attributes

		// If error is returned by API show it for few seconds in the Alt text field.
		if ( error ) {
			setAttributes( { alt: error } )
			setError( '' )
			setErrorClass( 'error' )
			setTimeout( () => {
				setAttributes( { alt } )
				setIsGenerating( false )
				setErrorClass( '' )
			}, 2000 )
		}

		// Function to handle the Alt text generation.
		const handleGenerateAltText = () => {
			if ( hasError() || rankMath.contentAICredits < 50 ) {
				showCTABox( { creditsRequired: 50 } )
				return
			}

			if ( ! url ) {
				setError( __( 'Image URL is missing.', 'rank-math' ) )
				return
			}

			setIsGenerating( true ) // Start loading
			setError( '' ) // Reset error message

			altTextGenerator( url, { value: alt } )
				.then( ( success ) => {
					if ( success ) {
						setAttributes( { alt: success } )
						setIsGenerating( false )
					} else {
						setError( __( 'Failed to generate alt text.', 'rank-math' ) )
					}
				} )
				.catch( ( err ) => {
					setError( err )
				} )
		}

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<Button
						variant="tertiary"
						size="compact"
						className={ errorClass + ' rank-math-generate-alt' }
						onClick={ handleGenerateAltText }
						disabled={ isGenerating }
					>
						{ errorClass && __( 'Failed', 'rank-math' ) }
						{
							! errorClass && isGenerating && __( 'Generating…', 'rank-math' ) }
						{
							! errorClass && ! isGenerating &&
							<>
								<i className="rm-icon rm-icon-content-ai"></i>
								{ __( 'Generate Alt', 'rank-math' ) }
							</>
						}
						{
							! isGenerating &&
							<span className="rank-math-tooltip">
								<em className="dashicons-before dashicons-editor-help"></em>
								<span>
									{ __( '50 Content AI credits will be used to generate the Alt', 'rank-math' ) }
								</span>
							</span>
						}
					</Button>
				</InspectorControls>
			</Fragment>
		)
	}
}, 'withInspectorControl' )
