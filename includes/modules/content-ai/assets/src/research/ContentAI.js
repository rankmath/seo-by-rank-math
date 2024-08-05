/* global alert */

/**
 * External dependencies
 */
import $ from 'jquery'
import { isEmpty, isNull, get, round, isString, isUndefined, isNumber, includes } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, Component } from '@wordpress/element'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { doAction, applyFilters } from '@wordpress/hooks'
import apiFetch from '@wordpress/api-fetch'
import {
	PanelBody,
	Button,
	SelectControl,
	TextControl,
} from '@wordpress/components'

/*
* Internal dependencies
*/
import ContentAIScore from './ContentAIScore'
import Recommendations from './Recommendations'
import ContentAIPanel from './ContentAIPanel'
import getLink from '@helpers/getLink'
import ErrorCTA from '@components/ErrorCTA'

class ContentAI extends Component {
	/**
	 * Constructor.
	 */
	constructor() {
		super( ...arguments )
		this.state = { keyword: this.props.keyword, showResearch: this.props.keyword !== rankMath.ca_keyword.keyword, country: this.props.country, credits: 1, loading: false }
		this.setState = this.setState.bind( this )
		this.isFree = isEmpty( rankMath.contentAIPlan ) || rankMath.contentAIPlan === 'free'
		this.hasCredits = rankMath.isUserRegistered && rankMath.ca_credits >= 500
	}

	/**
	 * Renders the component.
	 *
	 * @return {Component} ContentAI.
	 */
	render() {
		const showError = isEmpty( this.props.data ) && ( ! this.hasCredits || this.isFree )
		const className = classnames( 'rank-math-content-ai-data', {
			loading: this.state.loading,
			blurred: showError,
		} )

		let data = this.props.data
		if ( showError || 'show_dummy_data' === data ) {
			data = this.getDummyData()
		}

		return (
			<Fragment>
				<PanelBody className="rank-math-content-ai-wrapper research" initialOpen={ true }>
					<>
						{ ! showError && this.getHeader() }

						{ ! showError && this.keywordField() }

						<div className={ className }>
							<span className="loader-text">
								<span>{ __( 'Fetching Search Results', 'rank-math' ) }</span>
								<span>{ __( 'Analysing Your Competitors', 'rank-math' ) }</span>
								<span>{ __( 'Crunching the Numbers', 'rank-math' ) }</span>
								<span>{ __( 'Cooking a Personalized SEO Plan', 'rank-math' ) }</span>
								<span>{ __( 'Final Touches to the SEO Recommendations', 'rank-math' ) }</span>
							</span>
							{ isString( data ) && <h3 className="no-data" dangerouslySetInnerHTML={ { __html: data } }></h3> }
							{ ! isEmpty( data ) && ! isString( data ) && <div>
								<h3 className="rank-math-ca-section-title">
									{ __( 'Content AI', 'rank-math' ) }
									<span>{ __( 'New!', 'rank-math' ) }</span>
									<Button className="is-link" href={ getLink( 'content-ai-settings', 'Sidebar KB Icon' ) } rel="noreferrer" target="_blank" id="rank-math-help-icon" label={ __( 'Content AI Knowledge Base.', 'rank-math' ) } showTooltip={ true }>﹖</Button>
								</h3>
								<ContentAIScore />
								<Recommendations recommendations={ data.recommendations } showError={ showError } hasCredits={ this.hasCredits } content={ this.props.content } researcher={ this.props.researcher } updateAiScore={ this.props.updateAiScore } hasThumbnail={ this.props.hasThumbnail } />
								<ContentAIPanel caData={ this.props } updateAiScore={ this.props.updateAiScore } hasCredits={ this.hasCredits } showError={ showError } />
							</div>
							}
						</div>
						{ showError && <ErrorCTA isResearch={ true } /> }
					</>
				</PanelBody>
			</Fragment>
		)
	}

	/**
	 * Dummy data to show when user is out of credits or when the site is not connected with Rank Math.
	 */
	getDummyData() {
		return {
			keywords: {
				content: {
					'rank math': { keyword: 'rank math', average: 17, count: 12 },
					'rank math vs yoast seo': { keyword: 'rank math vs yoast seo', average: 1, count: 1 },
					'what is rank math': { keyword: 'what is rank math', average: 1, count: 1 },
					'rank math schema': { keyword: 'rank math schema', average: 1, count: 1 },
					'rank math configuration': { keyword: 'rank math configuration', average: 1, count: 1 },
					'rank math pro version': { keyword: 'rank math pro version', average: 1, count: 2 },
					'rank math comparison': { keyword: 'rank math comparison', average: 1, count: 1 },
					'rank math for seo': { keyword: 'rank math for seo', average: 1, count: 1 },
					'seo by rank math': { keyword: 'seo by rank math', average: 1, count: 0 },
				},
			},
			related_keywords: [
				'rank math plugin',
				'rank math pricing',
				'rank math vs yoast',
				'rank math review',
				'rank math premium',
				'how to use rank math',
				'rank math training',
				'rank math woocommerce',
				'wordpress seo plugin',
			],
			recommendations: {
				wordCount: 1829,
				linkCount: { total: 16 },
				headingCount: { total: 9 },
				mediaCount: { total: 18 },
			},
		}
	}

	/**
	 * Content AI Header with Back link and Country Dropdown.
	 */
	getHeader() {
		return (
			<div className="rank-math-ca-top-section">
				{
					includes( [ 'elementor', 'divi' ], rankMath.currentEditor ) &&
					<Button
						onClick={ () => ( $( '.rank-math-general-tab' ).trigger( 'click' ) ) }
					>
						<i className="dashicons dashicons-arrow-left-alt"></i>
						{ __( 'Back', 'rank-math' ) }
					</Button>
				}
				<SelectControl
					value={ this.state.country }
					onChange={ ( country ) => this.setState( { country, showResearch: true } ) }
					options={ rankMath.countries }
				/>
			</div>
		)
	}

	/**
	 * Get the keyword Field.
	 */
	keywordField() {
		return (
			<div className="rank-math-ca-keywords-wrapper">
				<div className="rank-math-ca-credits-wrapper">

					<TextControl
						label={ __( 'Focus Keyword', 'rank-math' ) }
						value={ this.state.keyword }
						onChange={ ( keyword ) => this.setState( { keyword, showResearch: true } ) }
						onKeyDown={ ( e ) => {
							if ( 'Enter' === e.key ) {
								e.preventDefault()
							}
						} }
						help={
							applyFilters(
								'rank_math_content_ai_help_text',
								(
									<>
										{ __( 'Upgrade to buy more credits from ', 'rank-math' ) }
										<a href={ getLink( 'content-ai-pricing-tables', 'Sidebar Upgrade Text' ) } rel="noreferrer" target="_blank" title={ __( 'Content AI Pricing.', 'rank-math' ) }>{ __( 'here.', 'rank-math' ) }</a>
									</>
								)
							)
						}
						placeholder={ __( 'Suggested length 2-3 Words', 'rank-math' ) }
					/>
					<div className="help-text">
						{ __( 'To learn how to use it', 'rank-math' ) } <a href={ getLink( 'content-ai-settings', 'Content AI Sidebar KB Link' ) } target="_blank" rel="noreferrer">{ __( 'Click here', 'rank-math' ) }</a>
					</div>
					{
						! this.state.showResearch && ! this.state.loading && ! isEmpty( this.props.data ) && this.hasCredits && ! this.isFree &&
						<Button
							className="rank-math-ca-force-update"
							onClick={ () => this.props.researchKeyword( this.state, this.setState, true ) }
							label={ __( 'Refresh will use 500 Credit.', 'rank-math' ) }
							showTooltip={ true }
						>
							<i className="dashicons dashicons-image-rotate"></i>
						</Button>
					}
				</div>

				{ this.state.showResearch && <Button
					className="is-primary"
					onClick={ () => this.props.researchKeyword( this.state, this.setState ) }
					label={ __( '500 credits will be used.', 'rank-math' ) }
					disabled={ ! this.state.keyword }
					showTooltip={ true }
				>
					{ __( 'Research', 'rank-math' ) }
				</Button> }
			</div>
		)
	}
}

export default compose(
	withSelect( ( select ) => {
		const researcher = rankMathEditor.assessor.analyzer.researcher
		const paper = researcher.paper
		return {
			data: select( 'rank-math' ).getKeywordsData(),
			keyword: get( rankMath.ca_keyword, 'keyword', select( 'rank-math' ).getKeywords().split( ',' )[ 0 ] ),
			country: get( rankMath.ca_keyword, 'country', rankMath.contentAiCountry ),
			researcher,
			content: ! isUndefined( paper ) ? paper.getTextLower() : '',
			title: ! isUndefined( paper ) ? paper.getTitle().toLowerCase() : '',
			description: ! isUndefined( paper ) ? paper.getDescription().toLowerCase() : '',
			hasThumbnail: ! isUndefined( paper ) ? paper.hasThumbnail() : '',
			score: select( 'rank-math' ).getContentAIScore(),
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		return {
			researchKeyword( data, setState, forceUpdate = false ) {
				data.force_update = forceUpdate
				data.objectID = rankMath.objectID
				data.objectType = rankMath.objectType
				setState( { showResearch: false, loading: true } )
				apiFetch( {
					method: 'POST',
					path: '/rankmath/v1/ca/researchKeyword',
					data,
				} )
					.catch( ( error ) => {
						setState( { loading: false, showResearch: true } )
						alert( error.message )
					} )
					.then( ( response ) => {
						setState( { loading: false } )
						dispatch( 'rank-math' ).updateKeywordsData( response.data )
						if ( ! isNull( response.credits ) && ! isUndefined( response.credits ) ) {
							setState( { credits: response.credits } )
							props.setCredits( ! isNumber( response.credits ) ? 0 : response.credits )
						}

						doAction( 'rank_math_content_ai_changed', response.keyword )
					} )
			},
			updateAiScore( key, score ) {
				const data = props.score
				data[ key ] = round( score, 2 )
				dispatch( 'rank-math' ).updateAIScore( data )
			},
		}
	} )
)( ContentAI )
