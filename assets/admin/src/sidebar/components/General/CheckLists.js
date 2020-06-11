/**
 * External dependencies
 */
import classnames from 'classnames'
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { PanelBody } from '@wordpress/components'
import { Fragment, Component } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Tooltip from '@components/Tooltip'
import getPartialClass from '@helpers/getPartialClass'

class CheckLists extends Component {
	shouldComponentUpdate( nextProps ) {
		if ( nextProps.isLoaded !== this.props.isLoaded ) {
			return true
		}

		if (
			nextProps.selectedKeyword.index !== this.props.selectedKeyword.index
		) {
			return true
		}

		if ( nextProps.isRefreshing !== this.props.isRefreshing ) {
			return true
		}

		return false
	}

	render() {
		const keyword = rankMathEditor.getSelectedKeyword()
		this.results = rankMathEditor.resultManager.getResult( keyword )
		this.isPrimary = 0 === this.props.selectedKeyword.index

		if ( isUndefined( this.results ) ) {
			return null
		}

		return Object.keys( this.getGroups() ).map( ( index ) =>
			this.renderGroup( index )
		)
	}

	renderGroup( index ) {
		this.errors = 0
		const listItems = this.renderGroupItems( index )

		return (
			<PanelBody
				key={ 'panel-' + index }
				title={
					<Fragment>
						{ this.getGroupTitle( index ) }
						{ 0 === this.errors ? (
							<span className="rank-math-group-score test-ok">
								{ __( 'All Good', 'rank-math' ) }
							</span>
						) : (
							<span className="rank-math-group-score test-fail">
								{ this.errors } { __( 'Errors', 'rank-math' ) }
							</span>
						) }
					</Fragment>
				}
				initialOpen={ 'basic' === index }
				className="rank-math-checklist"
			>
				<ul>{ listItems }</ul>
			</PanelBody>
		)
	}

	renderGroupItems( index ) {
		const results = this.results.results
		const groupItems = this.getGroupItems( index )

		return Object.keys( groupItems ).map( ( id ) => {
			if (
				isUndefined( results[ id ] ) ||
				( ! this.isPrimary && groupItems[ id ] )
			) {
				return false
			}

			const result = results[ id ]
			let classes = classnames( 'seo-check-' + id, {
				'test-ok': result.hasScore(),
				'test-fail': ! result.hasScore(),
			} )

			if (
				result.hasScore() &&
				[
					'contentHasAssets',
					'lengthContent',
					'keywordDensity',
				].includes( id )
			) {
				classes +=
					' ' +
					getPartialClass( result.getScore(), result.getMaxScore() )
			}

			if ( false === result.hasScore() ) {
				this.errors += 1
			}

			return (
				<li key={ id } className={ classes }>
					<span
						dangerouslySetInnerHTML={ { __html: result.getText() } }
					/>
					{ result.hasTooltip() ? (
						<Tooltip>{ result.getTooltip() }</Tooltip>
					) : null }
				</li>
			)
		} )
	}

	getGroupItems( group ) {
		const hash = {
			basic: {
				keywordInTitle: true,
				keywordInMetaDescription: true,
				keywordInPermalink: true,
				keywordIn10Percent: true,
				keywordInContent: false,
				lengthContent: false,
			},
			advanced: {
				keywordInSubheadings: false,
				keywordInImageAlt: true,
				keywordDensity: false,
				lengthPermalink: false,
				linksHasExternals: false,
				linksNotAllExternals: false,
				linksHasInternal: false,
				keywordNotUsed: true,
			},
			'title-readability': {
				titleStartWithKeyword: true,
				titleSentiment: false,
				titleHasPowerWords: false,
				titleHasNumber: false,
			},
			'content-readability': {
				contentHasTOC: false,
				contentHasShortParagraphs: false,
				contentHasAssets: false,
			},
		}

		return hash[ group ]
	}

	getGroupTitle( index ) {
		return this.getGroups()[ index ]
	}

	getGroups() {
		return {
			basic: __( 'Basic SEO', 'rank-math' ),
			advanced: __( 'Additional', 'rank-math' ),
			'title-readability': __( 'Title Readability', 'rank-math' ),
			'content-readability': __( 'Content Readability', 'rank-math' ),
		}
	}
}

export default withSelect( ( select ) => {
	const repo = select( 'rank-math' )
	return {
		isLoaded: repo.isLoaded(),
		isRefreshing: repo.isRefreshing(),
		selectedKeyword: repo.getSelectedKeyword(),
	}
} )( CheckLists )
