/**
 * External dependencies
 */
import classnames from 'classnames'
import { isObject, isUndefined, startCase, forEach, has, ceil } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { useState, useEffect } from '@wordpress/element'
import { withSelect } from '@wordpress/data'

/**
 * Internal dependencies
 */
import getClassByScore from '@helpers/getClassByScore'

const Recommendations = ( props ) => {
	const [ activeTab, setActiveTab ] = useState( '' )

	// TODO refactor this code.
	useEffect( () => {
		forEach( props.recommendations, ( recommendation, key ) => {
			if ( 'total' === key ) {
				return
			}

			const value = getPostStats( key )
			const recommended = ! isUndefined( recommendation.total ) ? recommendation.total : recommendation
			getScore( key, value, recommended, false, true )
		} )
	}, [ props.postStats ] )

	const getPostStats = ( key ) => {
		if ( has( props.postStats, key ) ) {
			return props.postStats[ key ]
		}

		return 0
	}

	const getScore = ( key, value, recommended, isSubMenu, forceUpdate = false ) => {
		// Early Bail if Research tab is disabled.
		if ( props.showError ) {
			return 0
		}

		let score = value === recommended ? 100 : ( value / recommended ) * 100

		if ( recommended === 0 && value <= 2 ) {
			score = 100
		}

		if ( isSubMenu ) {
			return score > 100 && score <= 125 ? 100 : score
		}

		let contentAiScore = score > 80 ? 100 : ( value / recommended ) * 80
		if ( score > 125 && 'wordCount' !== key ) {
			contentAiScore = 0
		}

		if ( forceUpdate ) {
			props.updateAiScore( key, contentAiScore )
		}

		if ( score > 100 && 'wordCount' === key ) {
			return 100
		}

		return score > 100 && score <= 125 ? 100 : score
	}

	const getRecommendations = ( recommendations, parentKey = null ) => {
		const data = []
		const isSubMenu = parentKey !== null

		forEach( recommendations, ( recommendation, key ) => {
			if ( 'total' === key ) {
				return
			}

			const recommended = ! isUndefined( recommendation.total ) ? recommendation.total : recommendation
			const postStat = getPostStats( key )
			const wrapperClasses = classnames( key, {
				'has-children': ! isUndefined( recommendation.total ),
				show: key === activeTab,
			} )

			const score = getScore( key, postStat, recommended, isSubMenu )
			const max = ceil( ( recommended * 150 ) / 100 )

			data.push(
				<div
					key={ key }
					className={ wrapperClasses + ' ' + getClassByScore( score ) }
					onClick={ () => setActiveTab( activeTab !== key ? key : '' ) }
					role="presentation"
				>
					<h4>{ ! isSubMenu ? startCase( key ) : key }</h4>
					{
						isSubMenu &&
						<span>
							{ postStat } / { recommended }
						</span>
					}
					{
						! isSubMenu &&
						<>
							<strong>{ postStat }</strong>
							<span className="desc">{ __( 'Use', 'rank-math' ) } { recommended } { __( 'to', 'rank-math' ) } { max }</span>
						</>
					}
					{ isObject( recommendation ) && getRecommendations( recommendation, key ) }
				</div>
			)
		} )

		return isSubMenu ? ( <div className="submenu">{ data }</div> ) : data
	}

	return (
		<div className="rank-math-ca-recommendations">
			{ getRecommendations( props.recommendations ) }
		</div>
	)
}

export default compose(
	withSelect( ( select, props ) => {
		let wordCount = 1518
		let images = 12
		let videos = 2
		let h2 = 5
		let h3 = 3
		let h4 = 0
		let h5 = 0
		let h6 = 0
		let internal = 8
		let external = 19

		if ( ! props.showError ) {
			const getWordCount = props.researcher.getResearch( 'wordCount' )
			const contentAssets = rankMathEditor.assessor.analyzer.defaultAnalyses.contentHasAssets
			wordCount = getWordCount( props.content )
			images = ! isUndefined( contentAssets ) && ! isUndefined( props.researcher.paper ) ? contentAssets.getImages( props.researcher.paper, props.content ) : 0
			videos = ! isUndefined( contentAssets ) ? contentAssets.getVideos( props.content ) : 0
			h2 = ( props.content.match( /<h2\b[^>]*>(.*?)<\/h2>/g ) || [] ).length
			h3 = ( props.content.match( /<h3\b[^>]*>(.*?)<\/h3>/g ) || [] ).length
			h4 = ( props.content.match( /<h4\b[^>]*>(.*?)<\/h4>/g ) || [] ).length
			h5 = ( props.content.match( /<h5\b[^>]*>(.*?)<\/h5>/g ) || [] ).length
			h6 = ( props.content.match( /<h6\b[^>]*>(.*?)<\/h6>/g ) || [] ).length

			const linkStats = props.researcher.getResearch( 'getLinkStats' )( props.content )
			internal = linkStats.internalTotal
			external = linkStats.externalTotal
		}

		return {
			...props,
			postStats: {
				wordCount,
				images,
				videos,
				mediaCount: images + videos,
				h2,
				h3,
				h4,
				h5,
				h6,
				headingCount: h2 + h3 + h4 + h5 + h6,
				internal,
				external,
				linkCount: internal + external,
			},
		}
	} )
)( Recommendations )
