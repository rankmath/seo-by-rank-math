/**
 * External dependencies
 */
import classnames from 'classnames'
import { has, includes, isUndefined, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'
import { PanelBody, Button } from '@wordpress/components'
import { Fragment, Component } from '@wordpress/element'

/**
 * Internal dependencies
 */
import highlightParagraph from './highlightParagraph'
import getPartialClass from '@helpers/getPartialClass'
import getLink from '@helpers/getLink'

class CheckLists extends Component {
	constructor() {
		super()
		this.state = { highlightText: true }
	}

	// Register annotator in Classic editor to highlight long paragraphs.
	componentDidMount() {
		if ( 'classic' !== rankMath.currentEditor ) {
			return false
		}

		const editor = window.tinymce.get( window.wpActiveEditor )
		if ( ! editor ) {
			return false
		}

		editor.annotator.register( 'rank-math-annotations', {
			persistent: false,
			decorate: () => ( { classes: [ 'rank-math-annotations' ] } ),
		} )

		editor.dom.addStyle( `
		.rank-math-annotations.mce-annotation {
			background-color: mark !important;
			position: relative;
		}
		.rank-math-annotations.mce-annotation .rank-math-content-ai-tooltip {
			display: none;
			background-color: #2271b1;
			border-color: #2271b1;
			color: #fff;
			cursor: pointer;
			position: absolute;
			top: -30px;
			left: 0px;
		}
		.rank-math-annotations.mce-annotation[data-mce-selected="inline-boundary"] .rank-math-content-ai-tooltip {
			display: inline-block;
		}
		` )
	}

	shouldComponentUpdate( nextProps, nextState ) {
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

		if ( nextState.highlightText !== this.state.highlightText ) {
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

			if (
				'contentHasShortParagraphs' === id &&
				includes( [ 'classic', 'gutenberg' ], rankMath.currentEditor ) &&
				! this.state.highlightText
			) {
				highlightParagraph(
					true,
					this.props.highlightedParagraphs,
					this.props.updateHighlightedParagraphs,
				)
			}

			return (
				<li key={ id } className={ classes }>
					<span
						dangerouslySetInnerHTML={ { __html: result.getText() } }
					/>
					{
						link &&
						<a
							href={ getLink( 'score-100', 'Content Analysis Single Test KB' ) + link }
							rel="noreferrer"
							target="_blank"
							className="dashicons-before dashicons-editor-help rank-math-help-icon"
						>
						</a>
					}
					{
						! result.hasScore() &&
						'contentHasShortParagraphs' === id &&
						! isEmpty( result.text ) &&
						includes( [ 'classic', 'gutenberg' ], rankMath.currentEditor ) &&
						<Button
							className="rank-math-highlight-button"
							onClick={ () => {
								highlightParagraph(
									this.state.highlightText,
									this.props.highlightedParagraphs,
									this.props.updateHighlightedParagraphs,
								)
								this.setState( {
									highlightText: ! this.state.highlightText,
								} )
							} }
						>
							{ this.state.highlightText && <i className="dashicons dashicons-visibility"></i> }
							{ ! this.state.highlightText && <i className="dashicons dashicons-hidden"></i> }
						</Button>
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

export default compose(
	withSelect( ( select ) => {
		const repo = select( 'rank-math' )
		return {
			isLoaded: repo.isLoaded(),
			isRefreshing: repo.isRefreshing(),
			selectedKeyword: repo.getSelectedKeyword(),
			highlightedParagraphs: repo.getHighlightedParagraphs(),
		}
	} ),
	withDispatch( ( dispatch ) => {
		return {
			updateHighlightedParagraphs( paragraphs ) {
				dispatch( 'rank-math' ).updateHighlightedParagraphs( paragraphs )
			},
		}
	} )
)( CheckLists )
