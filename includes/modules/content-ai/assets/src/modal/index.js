/**
 * External dependencies
 */
import jQuery from 'jquery'
import { reverse, find, map, startCase, isEmpty, isArray, isString, isObject } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { Button, Modal } from '@wordpress/components'
import { useState, useEffect, useCallback } from '@wordpress/element'
import { dispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import getFields from '../helpers/getFields'
import getData from '../helpers/getData'
import setDefaultValues from './setDefaultValues'
import getOutput from './getOutput'
import getTools from '../helpers/getTools'
import getEndpointHistory from './getEndpointHistory'
import closeModal from './closeModal'
import KBArticle from './KBArticle'
import ErrorCTA from '@components/ErrorCTA'
import Footer from './Footer'

const getWizardNavigation = ( steps ) => {
	const tools = [
		__( 'Post Title', 'rank-math' ),
		__( 'Post Outline', 'rank-math' ),
		__( 'Write Post', 'rank-math' ),
	]

	const endpoint = steps.endpoint

	return (
		<div className="wizard-navigation">
			<label className="dot-label">{ __( 'Steps', 'rank-math' ) }</label>
			{
				map( tools, ( tool, key ) => {
					let classname = ''
					if ( 0 === key ) {
						classname = 'active'
					}

					if ( 1 === key ) {
						classname = 'Blog_Post_Outline' === endpoint || 'Long_Form_Content' === endpoint ? 'active' : ''
					}

					if ( 'Long_Form_Content' === endpoint && key === 2 ) {
						classname = 'active'
					}

					return (
						<Button
							variant="link"
							className={ classname }
							href="#"
							label={ tool }
							showTooltip={ true }
						>
							<span></span>
						</Button>
					)
				} )
			}
		</div>
	)
}

/**
 * Content AI Tools modal.
 *
 * @param {Object} props Component props.
 */
export default ( props ) => {
	const { tool, setTool = false, isContentAIPage = false, callApi = false, plan } = props
	const { title, icon, output, helpLink } = tool
	let { endpoint, params } = tool
	let hasError = props.hasError
	let [ attributes, setAttributes ] = useState( setDefaultValues( params, output ) )
	const [ generating, setGenerating ] = useState()
	const [ isDisabled, setDisabled ] = useState()
	const [ showHistory, setHistory ] = useState( false )
	const [ results, setResults ] = useState( [] )
	const [ resultData, setData ] = useState( [] )
	const [ steps, setSteps ] = useState( { endpoint: 'Blog_Post_Idea' } )

	const endpointHistory = getEndpointHistory( endpoint )
	const originalEndpoint = endpoint
	const isWizard = 'Blog_Post_Wizard' === endpoint
	if ( isWizard ) {
		hasError = 'free' === plan ? true : hasError
		endpoint = 'Long_Form_Content' === steps.endpoint ? 'Blog_Post_Outline' : steps.endpoint
		const newTool = find( getTools(), [ 'endpoint', endpoint ] )
		if ( 'Blog_Post_Outline' === endpoint && ! isEmpty( steps.content ) ) {
			newTool.params.topic.default = steps.content
		}

		params = newTool.params
		output.default = 1
		if ( isEmpty( attributes.main_points ) ) {
			attributes = setDefaultValues( params, output )
		}
	}

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
			if ( originalEndpoint === 'Blog_Post_Wizard' ) {
				setData( '' )
			}

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
			title={ <><i className={ icon }></i> { title } { 'Blog_Post_Wizard' === originalEndpoint ? getWizardNavigation( steps ) : '' }</> }
			shouldCloseOnClickOutside={ true }
			onRequestClose={ ( e ) => ( closeModal( e, params, attributes, setTool ) ) }
		>
			<div className={ hasError ? 'columns column-body blurred' : 'columns column-body' }>
				<div className="column column-input">
					<div className="column-inner">
						{ getFields( params, attributes, endpoint, onChange, 'Blog_Post_Wizard' === originalEndpoint ) }
						<p className="required-fields"><i><span>*</span> { __( 'Required fields.', 'rank-math' ) }</i></p>
					</div>
					<Footer
						output={ output }
						attributes={ attributes }
						generating={ generating }
						results={ results }
						endpoint={ endpoint }
						originalEndpoint={ originalEndpoint }
						isDisabled={ isDisabled }
						steps={ steps }
						apiContent={ apiContent }
						setSteps={ setSteps }
						onChange={ onChange }
						setData={ setData }
						setResults={ setResults }
						setGenerating={ setGenerating }
						setDisabled={ setDisabled }
						setHistory={ setHistory }
					/>
				</div>
				<div className="column column-output">
					<div className="column-output-heading">
						<h3>
							<span>{ __( 'Output', 'rank-math' ) }</span>
							{ isWizard && <span>&nbsp;-&nbsp;{ startCase( steps.endpoint ) }</span> }
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
			{ hasError && <ErrorCTA width={ 60 } /> }
		</Modal>
	)
}
