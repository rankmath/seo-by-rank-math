/**
 * External dependencies
 */
import { isEmpty, isString, isUndefined, round } from 'lodash'
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withDispatch, withSelect } from '@wordpress/data'
import { Button } from '@wordpress/components'

/*
* Internal dependencies
*/
import ContentAIScore from './ContentAIScore'
import Recommendations from './Recommendations'
import ContentAIPanel from '../research/ContentAIPanel'
import getLink from '@helpers/getLink'

const getLoaderText = () => {
	return (
		<span className="loader-text">
			<span>{ __( 'Fetching Search Results', 'rank-math' ) }</span>
			<span>{ __( 'Analysing Your Competitors', 'rank-math' ) }</span>
			<span>{ __( 'Crunching the Numbers', 'rank-math' ) }</span>
			<span>{ __( 'Cooking a Personalized SEO Plan', 'rank-math' ) }</span>
			<span>{ __( 'Final Touches to the SEO Recommendations', 'rank-math' ) }</span>
		</span>
	)
}

const ResearchContent = ( props ) => {
	const researchedData = props.data.researchedData

	const wrapperClass = classnames(
		'rank-math-content-ai-data',
		{
			loading: props.loading,
			blurred: props.showError,
		}
	)

	return (
		<div className={ wrapperClass }>
			{ getLoaderText() }
			{ isString( researchedData ) && <h3 className="no-data" dangerouslySetInnerHTML={ { __html: researchedData } }></h3> }

			{ ! isEmpty( researchedData ) && ! isString( researchedData ) && <div>
				<h3 className="rank-math-ca-section-title">
					{ __( 'Content AI', 'rank-math' ) }
					<span>{ __( 'New!', 'rank-math' ) }</span>
					<Button className="is-link" href={ getLink( 'content-ai-settings', 'Sidebar KB Icon' ) } rel="noreferrer" target="_blank" id="rank-math-help-icon" label={ __( 'Content AI Knowledge Base.', 'rank-math' ) } showTooltip={ true }>﹖</Button>
				</h3>
				<ContentAIScore score={ props.score } />
				<Recommendations { ...props } recommendations={ researchedData.recommendations } />
				<ContentAIPanel { ...props } researchedData={ researchedData } />
			</div>
			}
		</div>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		const researcher = rankMathEditor.assessor.analyzer.researcher
		const paper = researcher.paper
		const focusKeyword = select( 'rank-math' ).getKeywords().split( ',' )[ 0 ] // TODO: Check why component doesn't re-render after removing this line.
		return {
			...props,
			score: select( 'rank-math-content-ai' ).getScore(),
			researcher,
			keyword: ! isEmpty( props.data.keyword ) ? props.data.keyword : focusKeyword,
			content: ! isUndefined( paper ) ? paper.getTextLower() : '',
			title: ! isUndefined( paper ) ? paper.getTitle().toLowerCase() : '',
			description: ! isUndefined( paper ) ? paper.getDescription().toLowerCase() : '',
			hasThumbnail: ! isUndefined( paper ) ? paper.hasThumbnail() : '',
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		return {
			updateAiScore( key, score ) {
				const newScore = props.data.score
				newScore[ key ] = round( score, 2 )
				dispatch( 'rank-math' ).updateAIScore( newScore )
			},
		}
	} )
)( ResearchContent )
