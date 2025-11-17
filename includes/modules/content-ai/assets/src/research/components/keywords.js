/**
 * External dependencies
 */
import { forEach, isEmpty, isString, isNull, isNaN, round, sum, includes, max } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment, useState, useEffect } from '@wordpress/element'
import { PanelBody, SelectControl, Button } from '@wordpress/components'
import { compose } from '@wordpress/compose'
import { withDispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { cleanTagsOnly } from '@helpers/cleanText'
import removeDiacritics from '@helpers/removeDiacritics'
import getClassByScore from '@helpers/getClassByScore'
import getLink from '@helpers/getLink'

const Keywords = ( props ) => {
	const [ type, setType ] = useState( 'content' )
	const [ selected, setSelected ] = useState( '' )

	useEffect( () => {
		initializeClipboard()
	}, [] )

	const updateContentAiScore = ( contentAiScore ) => {
		let score = 0
		let length = 0
		forEach( contentAiScore, ( keywords, keywordType ) => {
			const data = Object.values( keywords )
			let currentScore = sum( data )
			if (
				( 'title' === keywordType || 'description' === keywordType ) &&
				100 === max( data )
			) {
				currentScore = ( 100 * data.length )
			}

			score = score + currentScore
			length = length + data.length
		} )

		score = score / length
		if ( ! isNaN( score ) ) {
			props.updateAiScore( 'keywords', score )
		}
	}

	const getScore = ( keyword, value, recommended, keywordType, contentAiScore ) => {
		const score = ( value / recommended ) * 100
		if ( score > 100 ) {
			contentAiScore[ keywordType ][ keyword ] = 0
		} else {
			contentAiScore[ keywordType ][ keyword ] = score > 80 ? 100 : score
		}

		return score
	}

	const getCount = ( keyword, keywordType ) => {
		let content = props.content
		keyword = isString( keyword ) ? removeDiacritics( keyword ).toLowerCase() : keyword

		if ( 'heading' === keywordType ) {
			keyword = keyword.replace( /[\\^$*+?.()|[\]{}]/g, '\\$&' )
			const subheadingRegex = new RegExp( '<h[2-6][^>]*>.*?' + keyword + '.*?</h[2-6]>', 'g' )
			const count = ( content.match( subheadingRegex ) )
			return ! isNull( count ) ? count.length : 0
		}

		if ( 'title' === keywordType ) {
			content = props.title
		}

		if ( 'description' === keywordType ) {
			content = props.description
		}

		return cleanTagsOnly( content ).split( keyword ).length - 1
	}

	const getTooltipContent = ( data, isRelatedKeyword = false ) => {
		if ( props.showError ) {
			return
		}

		if ( selected === data.keyword ) {
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

	const getRelatedKeywords = () => {
		if ( 'content' !== type || isEmpty( props.researchedData.related_keywords ) ) {
			return false
		}

		const keywordData = []
		forEach( props.researchedData.related_keywords, ( value ) => {
			keywordData.push(
				<li
					className="rank-math-tooltip show"
					onClick={ () => setSelected( value ) }
					role="presentation"
				>
					<strong data-key={ value }>
						{ value }
					</strong>
					{ getTooltipContent( { keyword: value }, true ) }
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

	const getKeywords = () => {
		if ( isEmpty( props.researchedData.keywords ) ) {
			return (
				<h3 className="no-data">
					{ __( 'There are no recommended Keywords for this researched keyword.', 'rank-math' ) }
				</h3>
			)
		}

		const keywordData = []
		const contentAiScore = {}
		forEach( props.researchedData.keywords, ( keywords, keywordType ) => {
			if ( isEmpty( keywords ) ) {
				return
			}

			contentAiScore[ keywordType ] = {}
			forEach( keywords, ( data ) => {
				if ( isEmpty( data.keyword ) ) {
					return
				}

				const count = ! props.showError ? getCount( data.keyword, keywordType ) : data.count
				const scoreClass = getClassByScore( getScore( data.keyword, count, data.average, keywordType, contentAiScore ) )
				const className = classnames( 'rank-math-tooltip', {
					show: keywordType === type,
				} )

				keywordData.push(
					<li
						className={ className + ' ' + scoreClass }
						onClick={ () => setSelected( data.keyword ) }
						role="presentation"
					>
						<strong data-key={ data.keyword }>
							{ data.keyword }
							<span>{ count } / { data.average }</span>
						</strong>
						{ getTooltipContent( data ) }
					</li>
				)
			} )
		} )

		updateContentAiScore( contentAiScore )

		return keywordData
	}

	const initializeClipboard = () => {
		if ( 'function' !== typeof ClipboardJS || props.showError ) {
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
				setSelected( '' )
			}, 3000 )
		} )
	}
	return (
		<Fragment>
			<PanelBody initialOpen={ true }>
				<SelectControl
					label={ __( 'Use Keyword in', 'rank-math' ) }
					value={ type }
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
					onChange={ ( type ) => setType( type ) }
				/>
				<Button className="is-link" href={ getLink( 'content-ai-keywords', 'Sidebar Keywords KB Icon' ) } rel="noreferrer" target="_blank" id="rank-math-help-icon" label={ __( 'Know more about Keywords.', 'rank-math' ) } showTooltip={ true }>﹖</Button>
				<>
					<span className="components-form-token-field__help">{ __( 'Click on any keyword to copy it.', 'rank-math' ) }</span>
					<br />
					{
						includes( [ 'title', 'description' ], type ) &&
						<span className="components-form-token-field__help">{ __( 'Please use only one or two keywords from here.', 'rank-math' ) }</span>
					}
					<ul>
						{ getKeywords() }
					</ul>
				</>

				{ getRelatedKeywords() }

			</PanelBody>
		</Fragment>
	)
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
