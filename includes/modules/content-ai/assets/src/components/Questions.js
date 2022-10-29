/**
 * External dependencies
 */
import { forEach, uniqueId, isEmpty, isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, Component } from '@wordpress/element'
import { PanelBody, Button } from '@wordpress/components'

/**
 * Internal dependencies
 */
import isGutenbergAvailable from '@helpers/isGutenbergAvailable'

class Questions extends Component {
	/**
	 * Constructor.
	 */
	constructor() {
		super( ...arguments )
		this.state = { iconClass: 'rm-icon-copy', selected: '' }
		this.setState = this.setState.bind( this )
		this.initializeClipboard( this.setState )
	}

	/**
	 * Initialize the Clipboard when class is executed.
	 *
	 * @param {Component.setState} setState Updates the current component state.
	 */
	initializeClipboard( setState ) {
		if ( 'function' !== typeof ClipboardJS ) {
			return
		}

		const clipboard = new ClipboardJS(
			'.rank-math-copy-questions, .rank-math-questions-item h3',
			{
				text: ( target ) => {
					if ( ! isNull( target.getAttribute( 'data-key' ) ) ) {
						return target.getAttribute( 'data-key' )
					}

					if ( ! isGutenbergAvailable() ) {
						return document.getElementById( 'rank-math-ca-questions-data' ).innerHTML
					}

					const questions = []
					forEach( this.props.caData.data.related_questions, ( data ) => {
						questions.push(
							{
								id: uniqueId( 'faq-question-' ),
								title: data,
								content: '',
								visible: true,
							}
						)
					} )

					return '<!-- wp:rank-math/faq-block ' + JSON.stringify( { questions } ) + ' --><div class="wp-block-rank-math-faq-block"></div><!-- /wp:rank-math/faq-block -->'
				},
			}
		)

		clipboard.on( 'success', function() {
			setTimeout( () => {
				setState( { iconClass: 'rm-icon-copy' } )
			}, 3000 )
		} )
	}

	/**
	 * Renders the component.
	 *
	 * @return {Component} Questions.
	 */
	render() {
		const questionsData = []
		if ( isEmpty( this.props.caData.data.related_questions ) ) {
			return (
				<h3 className="no-data">
					{ __( 'There are no recommended Questions for this researched keyword.', 'rank-math' ) }
				</h3>
			)
		}

		forEach( this.props.caData.data.related_questions, ( question ) => {
			questionsData.push(
				<div className="rank-math-questions-item">
					<h3 className="rank-math-tooltip" data-key={ question } onClick={ () => ( this.setState( { selected: question } ) ) } role="presentation">
						{ question }
						{ this.getTooltipContent( question ) }
					</h3>
				</div>
			)
		} )

		return (
			<Fragment>
				<PanelBody initialOpen={ true }>
					<div className="rank-math-section-heading">
						<h2>
							{ __( 'Related Questions', 'rank-math' ) }
							<a href={ getLink( 'content-ai-settings', 'Sidebar Questions KB Icon' ) } rel="noreferrer" target="_blank" id="rank-math-help-icon" title={ __( 'Know more about Questions.', 'rank-math' ) }>﹖</a>
						</h2>
						<Button
							onClick={ () => {
								this.setState( { iconClass: 'rm-icon-tick' } )
							} }
							className="rank-math-copy-questions button-secondary rank-math-tooltip left"
						>
							<i className={ 'rm-icon ' + this.state.iconClass }></i>
							<span>{ __( 'Copy this data as a FAQ Block.', 'rank-math' ) }</span>
						</Button>
					</div>

					<span className="components-form-token-field__help">{ __( 'Click on any question to copy it.', 'rank-math' ) }</span>

					<div id="rank-math-ca-questions-data">
						{ questionsData }
					</div>
				</PanelBody>
			</Fragment>
		)
	}

	getTooltipContent( question ) {
		if ( this.state.selected !== question ) {
			return false
		}

		return (
			<span className="rank-math-tooltip-data">{ __( 'Copied', 'rank-math' ) }</span>
		)
	}
}

export default Questions
