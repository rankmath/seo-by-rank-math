/**
 * External dependencies
 */
import { forEach, isEmpty, isNull, isNaN, round, sum, includes, max } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, Component } from '@wordpress/element'
import { PanelBody, SelectControl } from '@wordpress/components'
import { compose } from '@wordpress/compose'
import { withDispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { Helpers } from '@rankMath/analyzer'
import getClassByScore from '@helpers/getClassByScore'

class Keywords extends Component {
	/**
	 * Constructor.
	 */
	constructor() {
		super( ...arguments )
		this.state = { type: 'content', selected: '' }
		this.setState = this.setState.bind( this )
		this.initializeClipboard( this.setState )
	}

	/**
	 * Initialize the Clipboard when class is executed.
	 *
	 * @param {Component.setState} setState Updates the current component state.
	 */
	initializeClipboard( setState ) {
		if ( 'function' !== typeof ClipboardJS || ! this.props.hasCredits ) {
			return
		}

		const clipboard = new ClipboardJS(
			'.rank-math-tooltip strong',
			{
				text: ( trigger ) => {
					return trigger.getAttribute( 'data-key' )
				},
			}
		)

		clipboard.on( 'success', function() {
			setTimeout( () => {
				setState( { selected: '' } )
			}, 3000 )
		} )
	}

	/**
	 * Renders the component.
	 *
	 * @return {Component} Keywords.
	 */
	render() {
		return (
			<Fragment>
				<PanelBody initialOpen={ true }>
					<SelectControl
						label={ __( 'Use Keyword in', 'rank-math' ) }
						value={ this.state.type }
						options={ [
							{
								value: 'content',
								label: __( 'Content', 'rank-math' ),
							},
							{
								value: 'heading',
								label: __( 'Headings', 'rank-math' ),
							},
							{
								value: 'title',
								label: __( 'SEO Title', 'rank-math' ),
							},
							{
								value: 'description',
								label: __( 'SEO Description', 'rank-math' ),
							},
						] }
						onChange={ ( type ) => {
							this.setState( { type } )
						} }
					/>
					<a href="https://rankmath.com/kb/how-to-use-content-ai/?utm_source=Plugin&utm_medium=Sidebar%20Keywords%20KB%20Icon&utm_campaign=WP#keywords" rel="noreferrer" target="_blank" id="rank-math-help-icon" title={ __( 'Know more about Keywords.', 'rank-math' ) }>﹖</a>
					<>
						<span className="components-form-token-field__help">{ __( 'Click on any keyword to copy it.', 'rank-math' ) }</span>
						<br />
						{
							includes( [ 'title', 'description' ], this.state.type ) &&
							<span className="components-form-token-field__help">{ __( 'Please use only one or two keywords from here.', 'rank-math' ) }</span>
						}
						<ul>
							{ this.getKeywords() }
						</ul>
					</>

					{ this.getRelatedKeywords() }

				</PanelBody>
			</Fragment>
		)
	}

	getRelatedKeywords() {
		if ( 'content' !== this.state.type || isEmpty( this.props.caData.data.related_keywords ) ) {
			return false
		}

		const keywordData = []
		forEach( this.props.caData.data.related_keywords, ( value ) => {
			keywordData.push(
				<li
					className="rank-math-tooltip show"
					onClick={ () => ( this.setState( { selected: value } ) ) }
					role="presentation"
				>
					<strong data-key={ value }>
						{ value }
					</strong>
					{ this.getTooltipContent( { keyword: value }, true ) }
				</li>
			)
		} )

		return (
			<div className="rank-math-related-keywords">
				<h3>{ __( 'Related Keywords', 'rank-math' ) }</h3>
				<ul>{ keywordData }</ul>
			</div>
		)
	}

	getKeywords() {
		if ( isEmpty( this.props.caData.data.keywords ) ) {
			return (
				<h3 className="no-data">
					{ __( 'There are no recommended Keywords for this researched keyword.', 'rank-math' ) }
				</h3>
			)
		}

		const keywordData = []
		this.contentAiScore = {}
		forEach( this.props.caData.data.keywords, ( keywords, type ) => {
			if ( isEmpty( keywords ) ) {
				return
			}

			this.contentAiScore[ type ] = {}
			forEach( keywords, ( data ) => {
				const count = this.props.hasCredits ? this.getCount( data.keyword, type ) : data.count
				const scoreClass = getClassByScore( this.getScore( data.keyword, count, data.average, type ) )
				const className = classnames( 'rank-math-tooltip', {
					show: this.state.type === type,
				} )

				keywordData.push(
					<li
						className={ className + ' ' + scoreClass }
						onClick={ () => ( this.setState( { selected: data.keyword } ) ) }
						role="presentation"
					>
						<strong data-key={ data.keyword }>
							{ data.keyword }
							<span>{ count } / { data.average }</span>
						</strong>
						{ this.getTooltipContent( data ) }
					</li>
				)
			} )
		} )

		this.updateContentAiScore()

		return keywordData
	}

	updateContentAiScore() {
		let score = 0
		let length = 0
		forEach( this.contentAiScore, ( keywords, type ) => {
			const data = Object.values( keywords )
			let currentScore = sum( data )
			if (
				( 'title' === type || 'description' === type ) &&
				100 === max( data )
			) {
				currentScore = ( 100 * data.length )
			}

			score = score + currentScore
			length = length + data.length
		} )

		score = score / length
		if ( ! isNaN( score ) ) {
			this.props.updateAiScore( 'keywords', score )
		}
	}

	getScore( keyword, value, recommended, type ) {
		const score = ( value / recommended ) * 100
		if ( score > 100 ) {
			this.contentAiScore[ type ][ keyword ] = 0
		} else {
			this.contentAiScore[ type ][ keyword ] = score > 80 ? 100 : score
		}

		return score
	}

	getTooltipContent( data, isRelatedKeyword = false ) {
		if ( ! this.props.hasCredits ) {
			return
		}

		if ( this.state.selected === data.keyword ) {
			return (
				<span className="rank-math-tooltip-data">{ __( 'Copied', 'rank-math' ) }</span>
			)
		}

		if ( ! data.competition && ! data.cpc && ! data.search_volume ) {
			return (
				<span className="rank-math-tooltip-data">
					{ isRelatedKeyword ? __( 'Click to copy keyword', 'rank-math' ) : __( 'No data available', 'rank-math' ) }
				</span>
			)
		}

		return (
			<span className="rank-math-tooltip-data">
				{
					<span>{ __( 'Ad Competition:', 'rank-math' ) } { round( data.competition * 100 ) }</span>
				}

				{
					<span>{ __( 'CPC:', 'rank-math' ) } ${ round( data.cpc, 2 ) }</span>
				}

				{
					<span>{ __( 'Volume:', 'rank-math' ) } { round( data.search_volume ) }</span>
				}
			</span>
		)
	}

	getCount( keyword, type ) {
		let content = this.props.caData.content
		keyword = Helpers.removeDiacritics( keyword ).toLowerCase()

		if ( 'heading' === type ) {
			keyword = keyword.replace( /[\\^$*+?.()|[\]{}]/g, '\\$&' )
			const subheadingRegex = new RegExp( '<h[2-6][^>]*>.*?' + keyword + '.*?</h[2-6]>', 'g' )
			const count = ( content.match( subheadingRegex ) )
			return ! isNull( count ) ? count.length : 0
		}

		if ( 'title' === type ) {
			content = this.props.caData.title
		}

		if ( 'description' === type ) {
			content = this.props.caData.description
		}

		return Helpers.cleanTagsOnly( content ).split( keyword ).length - 1
	}
}

export default compose(
	withDispatch( ( dispatch ) => {
		return {
			toggleEditor() {
				dispatch( 'rank-math' ).toggleSnippetEditor( true )
			},
		}
	} )
)( Keywords )
