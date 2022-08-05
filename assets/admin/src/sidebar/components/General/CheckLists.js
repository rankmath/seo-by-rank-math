/**
 * External dependencies
 */
import classnames from 'classnames'
import { isUndefined, has } from 'lodash'

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

			const link = this.getLink( id )
			return (
				<li key={ id } className={ classes }>
					<span
						dangerouslySetInnerHTML={ { __html: result.getText() } }
					/>
					{
						link &&
						<a
							href={ 'https://rankmath.com/kb/score-100-in-tests/?utm_source=Plugin&utm_medium=Content%20Analysis%20Single%20Test%20KB&utm_campaign=WP' + link }
							rel="noreferrer"
							target="_blank"
							className="dashicons-before dashicons-editor-help rank-math-help-icon"
						>
						</a>
					}
				</li>
			)
		} )
	}

	getGroupItems( group ) {
		let hash = ''

		if ( 'post' === rankMath.objectType ) {
			hash = {
				basic: {
					keywordInTitle: true,
					keywordInMetaDescription: true,
					keywordInPermalink: true,
					keywordIn10Percent: true,
					keywordInContent: false,
					lengthContent: false,
					hasProductSchema: true,
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
					hasContentAI: true,
					isReviewEnabled: true,
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
		} else {
			hash = {
				basic: {
					keywordInTitle: true,
					keywordInMetaDescription: true,
					keywordInPermalink: true,
				},
				advanced: {
					titleStartWithKeyword: true,
					keywordNotUsed: true,
				},
			}
		}

		return hash[ group ]
	}

	getGroupTitle( index ) {
		return this.getGroups()[ index ]
	}

	getGroups() {
		if ( 'post' === rankMath.objectType ) {
			return {
				basic: __( 'Basic SEO', 'rank-math' ),
				advanced: __( 'Additional', 'rank-math' ),
				'title-readability': __( 'Title Readability', 'rank-math' ),
				'content-readability': __( 'Content Readability', 'rank-math' ),
			}
		}

		return {
			basic: __( 'Basic SEO', 'rank-math' ),
			advanced: __( 'Additional', 'rank-math' ),
		}
	}

	getLink( id ) {
		const links = {
			keywordInTitle: '#focus-keyword-in-the-seo-title-primary-focus-keyword-only',
			keywordInMetaDescription: '#focus-keyword-in-the-meta-description-primary-focus-keyword-only',
			keywordInPermalink: '#focus-keyword-in-the-url-primary-focus-keyword-only',
			keywordIn10Percent: '#focus-keyword-at-the-beginning-of-the-content',
			keywordInContent: '#focus-keyword-in-the-content-runs-of-all-focus-keywords',
			lengthContent: '#overall-content-length',
			keywordInSubheadings: '#focus-keyword-in-subheading-primary-and-secondary-focus-keywords',
			keywordInImageAlt: '#focus-keyword-in-image-alt-attributes-primary-focus-keyword-only',
			keywordDensity: '#keyword-density-primary-and-secondary-focus-keywords',
			lengthPermalink: '#url-length',
			linksHasExternals: '#linking-to-external-sources',
			linksNotAllExternals: '#linking-to-external-content-with-a-followed-link',
			linksHasInternal: '#linking-to-internal-resources',
			keywordNotUsed: '#focus-keyword-uniqueness-primary-focus-keyword-only',
			titleStartWithKeyword: '#focus-keyword-at-the-beginning-of-the-seo-title-only-for-primary-keyword',
			titleSentiment: '#sentiment-in-a-title',
			titleHasPowerWords: '#use-of-power-word-in-title',
			titleHasNumber: '#number-in-title',
			contentHasTOC: '#table-of-contents',
			contentHasShortParagraphs: '#use-of-short-paragraphs',
			contentHasAssets: '#use-of-media-in-your-posts',
			hasContentAI: '#used-content-ai',
			hasProductSchema: '#has-product-schema',
			isReviewEnabled: '#is-review-enabled',
		}

		return has( links, id ) ? links[ id ] : ''
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
