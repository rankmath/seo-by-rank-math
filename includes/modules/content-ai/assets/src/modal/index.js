/**
 * External dependencies
 */
import jQuery from 'jquery'
import { reverse, isEmpty, isArray, isString, isObject } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import {
	Button,
	Modal,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components'
import { useState, useEffect, useCallback } from '@wordpress/element'
import { dispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import getFields from '../helpers/getFields'
import getData from '../helpers/getData'
import setDefaultValues from './setDefaultValues'
import getOutput from './getOutput'
import getEndpointHistory from './getEndpointHistory'
import closeModal from './closeModal'
import KBArticle from './KBArticle'
import ErrorCTA from '@components/ErrorCTA'

/**
 * Content AI Tools modal.
 *
 * @param {Object} props Component props.
 */
export default ( props ) => {
	const { tool, setTool = false, isContentAIPage = false, callApi = false } = props
	const { endpoint, title, params, icon, output, helpLink } = tool
	const [ attributes, setAttributes ] = useState( setDefaultValues( params, output ) )
	const [ generating, setGenerating ] = useState()
	const [ isDisabled, setDisabled ] = useState()
	const [ showHistory, setHistory ] = useState( false )
	const [ results, setResults ] = useState( [] )
	const [ resultData, setData ] = useState( [] )

	const endpointHistory = getEndpointHistory( endpoint )

	useEffect( () => {
		if ( ! isArray( results ) || 'Frequently_Asked_Questions' === endpoint ) {
			setData( results )
			return
		}

		if ( isString( resultData ) ) {
			setData( '' )
		}

		let delay = 0
		for ( let i = 0; i <= results.length - 1; i++ ) {
			if ( i > 0 ) {
				const content = isObject( results[ i - 1 ] ) ? Object.values( results[ i - 1 ] ).join( ' ' ) : results[ i - 1 ]
				delay = delay + ( content.split( ' ' ).length * 110 )
			}

			setTimeout( () => setData( ( prevState ) => [ ...prevState, results[ i ] ] ), delay )
		}
	}, [ results ] )

	useEffect( () => {
		if ( callApi ) {
			setGenerating( true )
			setDisabled( true )
			setHistory( false )
			getData( endpoint, attributes, apiContent )
		}
	}, [] )

	const apiContent = ( value ) => {
		if ( ! isEmpty( value.error ) ) {
			setResults( '<div class="notice notice-error">' + value.error + '</div>' )
		} else {
			setResults( value.faqs ? value.faqs : value )
		}

		setGenerating( false )
		setDisabled( false )
	}

	const onChange = useCallback( ( key, value ) => {
		attributes[ key ] = value
		setAttributes( { ...attributes } )

		dispatch( 'rank-math-content-ai' ).updateAIAttributes( key, value )

		setTimeout( () => {
			setDisabled( jQuery( 'form.rank-math-ai-tools' ).find( '.limit-reached' ).length )
		}, 500 )
	}, [] )

	return (
		<Modal
			className="rank-math-contentai-modal rank-math-modal"
			overlayClassName="rank-math-modal-overlay rank-math-contentai-modal-overlay"
			title={ <><i className={ icon }></i> { title }</> }
			shouldCloseOnClickOutside={ true }
			onRequestClose={ ( e ) => ( closeModal( e, params, attributes, setTool ) ) }
		>
			<div className={ props.hasError ? 'columns column-body blurred' : 'columns column-body' }>
				<div className="column column-input">
					<div className="column-inner">
						{ getFields( params, attributes, endpoint, onChange ) }
						<p className="required-fields"><i><span>*</span> { __( 'Required fields.', 'rank-math' ) }</i></p>
					</div>
					<div className="footer">
						<NumberControl
							min="1"
							max={ output.max }
							value={ attributes.choices ?? output.default }
							onChange={ ( newChoice ) => ( onChange( 'choices', newChoice ) ) }
						/>
						<span className="output-label">{ __( 'Outputs', 'rank-math' ) }</span>
						<Button
							className="button button-primary"
							disabled={ isDisabled }
							onClick={ () => {
								const form = jQuery( 'form.rank-math-ai-tools' ).get( 0 )
								if ( ! form.checkValidity() ) {
									form.reportValidity()
									return
								}
								setGenerating( true )
								setDisabled( true )
								setHistory( false )
								getData( endpoint, attributes, apiContent )
							} }
						>
							<span className="text">
								{
									generating ? __( 'Generating…', 'rank-math' ) : ( ! isEmpty( results ) ? __( 'Generate More', 'rank-math' ) : __( 'Generate', 'rank-math' ) )
								}
							</span>
						</Button>
					</div>
				</div>
				<div className="column column-output">
					<div className="column-output-heading">
						<h3>
							<span>{ __( 'Output', 'rank-math' ) }</span>
						</h3>

						{
							! isEmpty( endpointHistory ) &&
							<Button
								className="button button-secondary button-small output-history"
								onClick={ () => {
									setHistory( ! showHistory )
								} }
							>
								{ __( 'History', 'rank-math' ) }
							</Button>
						}
					</div>
					{ generating && (
						<div className="inner-wrapper">
							<div className="output-item loading">
								<div className="rank-math-loader"></div>
							</div>
						</div>
					) }
					{
						! generating && ! showHistory && isEmpty( results ) &&
						<>
							<p style={ { fontSize: '1rem', marginTop: 0 } }>{ __( 'Suggestions will appear here.', 'rank-math' ) }</p>
							{ helpLink && <KBArticle helpLink={ helpLink } title={ title } /> }
						</>
					}

					{ ! showHistory && getOutput( resultData, isContentAIPage, endpoint, true, callApi ) }
					{ ! generating && showHistory && getOutput( reverse( endpointHistory ), isContentAIPage, endpoint, false, callApi ) }
					{
						! showHistory && callApi && ! generating &&
						<div className="notice notice-info"
							dangerouslySetInnerHTML={ {
								__html: sprintf(
									// Translators: Link to Content AI page.
									__( '%s to access all the Content AI tools', 'rank-math' ), '<a href="' + rankMath.adminurl + '?page=rank-math-content-ai-page#ai-tools" target="_blank">' + __( 'Click here', 'rank-math' ) + '</a>'
								),
							} }
						></div>
					}
				</div>
			</div>
			{ props.hasError && <ErrorCTA width={ 60 } /> }
		</Modal>
	)
}
