/**
 * External dependencies
 */
import classnames from 'classnames'
import { Helpers } from '@rankMath/analyzer'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { withSelect } from '@wordpress/data'
import { safeDecodeURIComponent } from '@wordpress/url'

/**
 * Internal dependencies
 */
import PreviewDevices from './PreviewDevices'
import AnalysisScore from '@components/AnalysisScore'
import highlight from '@helpers/highlight';
import RatingPreview from "./RatingPreview";

const SerpPreview = ( {
	title,
	permalink,
	description,
	previewType = 'desktop',
	isNoIndex,
	keyword,
	onClick,
	showScore = true,
	showDevices = false,
} ) => {
	const classes = classnames( 'serp-preview', {
		'expanded-preview': '' !== previewType,
		[ `${ previewType }-preview` ]: '' !== previewType && showDevices,
		'noindex-preview': isNoIndex,
	} )

	const titleClasses = classnames( 'serp-title', {
		capitalize: rankMath.capitalizeTitle,
	} )

	const keywordPermalink = rankMathEditor.assessor.getResearch( 'slugify' )(
		keyword
	)

	return (
		<div className={ classes }>
			<div
				className="serp-preview-title"
				data-title={ __( 'Preview', 'rank-math' ) }
				data-desktop={ __( 'Desktop Preview', 'rank-math' ) }
				data-mobile={ __( 'Mobile Preview', 'rank-math' ) }
			>
				{ showScore && <AnalysisScore /> }{ ' ' }
				{ showDevices && <PreviewDevices /> }
			</div>

			<div className="serp-preview-wrapper">
				<div className="serp-preview-bg">
					<div className="serp-preview-input">
						<input
							type="text"
							value={
								keyword
									? keyword
									: __( 'Rank Math', 'rank-math' )
							}
							disabled
						/>

						<span className="serp-icon-search">
							<svg
								focusable="false"
								xmlns="http://www.w3.org/2000/svg"
								viewBox="0 0 24 24"
							>
								<path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
							</svg>
						</span>

						<span className="serp-icon-mic"></span>
					</div>

					<div className="serp-preview-menus">
						<ul>
							<li className="current">
								<img
									src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0ibm9uZSIgZD0iTTAgMGgyNHYyNEgweiIvPjxwYXRoIGZpbGw9IiMzNEE4NTMiIGQ9Ik0xMCAydjJhNiA2IDAgMCAxIDYgNmgyYTggOCAwIDAgMC04LTh6Ii8+PHBhdGggZmlsbD0iI0VBNDMzNSIgZD0iTTEwIDRWMmE4IDggMCAwIDAtOCA4aDJjMC0zLjMgMi43LTYgNi02eiIvPjxwYXRoIGZpbGw9IiNGQkJDMDQiIGQ9Ik00IDEwSDJhOCA4IDAgMCAwIDggOHYtMmMtMy4zIDAtNi0yLjY5LTYtNnoiLz48cGF0aCBmaWxsPSIjNDI4NUY0IiBkPSJNMjIgMjAuNTlsLTUuNjktNS42OUE3Ljk2IDcuOTYgMCAwIDAgMTggMTBoLTJhNiA2IDAgMCAxLTYgNnYyYzEuODUgMCAzLjUyLS42NCA0Ljg4LTEuNjhsNS42OSA1LjY5TDIyIDIwLjU5eiIvPjwvc3ZnPgo="
									alt=""
									data-atf="1"
								/>
								{ __( 'All', 'rank-math' ) }
							</li>
							<li>
								<svg
									focusable="false"
									viewBox="0 0 24 24"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path d="M0 0h24v24H0z" fill="none"></path>
									<path d="M14 13l4 5H6l4-4 1.79 1.78L14 13zm-6.01-2.99A2 2 0 0 0 8 6a2 2 0 0 0-.01 4.01zM22 5v14a3 3 0 0 1-3 2.99H5c-1.64 0-3-1.36-3-3V5c0-1.64 1.36-3 3-3h14c1.65 0 3 1.36 3 3zm-2.01 0a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h7v-.01h7a1 1 0 0 0 1-1V5z"></path>
								</svg>{ ' ' }
								{ __( 'Images', 'rank-math' ) }
							</li>
							<li>
								<svg
									focusable="false"
									viewBox="0 0 24 24"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path
										clipRule="evenodd"
										d="M0 0h24v24H0z"
										fill="none"
									></path>
									<path
										clipRule="evenodd"
										d="M10 16.5l6-4.5-6-4.5v9zM5 20h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1zm14.5 2H5a3 3 0 0 1-3-3V4.4A2.4 2.4 0 0 1 4.4 2h15.2A2.4 2.4 0 0 1 22 4.4v15.1a2.5 2.5 0 0 1-2.5 2.5z"
										fillRule="evenodd"
									></path>
								</svg>{ ' ' }
								{ __( 'Videos', 'rank-math' ) }
							</li>
							<li>
								<svg
									focusable="false"
									viewBox="0 0 24 24"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path d="M0 0h24v24H0z" fill="none"></path>
									<path d="M12 11h6v2h-6v-2zm-6 6h12v-2H6v2zm0-4h4V7H6v6zm16-7.22v12.44c0 1.54-1.34 2.78-3 2.78H5c-1.64 0-3-1.25-3-2.78V5.78C2 4.26 3.36 3 5 3h14c1.64 0 3 1.25 3 2.78zM19.99 12V5.78c0-.42-.46-.78-1-.78H5c-.54 0-1 .36-1 .78v12.44c0 .42.46.78 1 .78h14c.54 0 1-.36 1-.78V12zM12 9h6V7h-6v2z"></path>
								</svg>{ ' ' }
								{ __( 'News', 'rank-math' ) }
							</li>
							<li>
								<svg focusable="false" viewBox="0 0 16 16">
									<path d="M7.503 0c3.09 0 5.502 2.487 5.502 5.427 0 2.337-1.13 3.694-2.26 5.05-.454.528-.906 1.13-1.358 1.734-.452.603-.754 1.508-.98 1.96-.226.452-.377.829-.904.829-.528 0-.678-.377-.905-.83-.226-.451-.527-1.356-.98-1.959-.452-.603-.904-1.206-1.356-1.734C3.132 9.121 2 7.764 2 5.427 2 2.487 4.412 0 7.503 0zm0 1.364c-2.283 0-4.14 1.822-4.14 4.063 0 1.843.86 2.873 1.946 4.177.468.547.942 1.178 1.4 1.79.34.452.596.99.794 1.444.198-.455.453-.992.793-1.445.459-.61.931-1.242 1.413-1.803 1.074-1.29 1.933-2.32 1.933-4.163 0-2.24-1.858-4.063-4.139-4.063zm0 2.734a1.33 1.33 0 11-.001 2.658 1.33 1.33 0 010-2.658"></path>
								</svg>{ ' ' }
								{ __( 'Maps', 'rank-math' ) }
							</li>
							<li>
								<svg
									focusable="false"
									xmlns="http://www.w3.org/2000/svg"
									viewBox="0 0 24 24"
								>
									<path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path>
								</svg>{ ' ' }
								{ __( 'More', 'rank-math' ) }
							</li>
						</ul>

						<ul className="menus-right">
							<li>{ __( 'Settings', 'rank-math' ) }</li>
							<li>{ __( 'Tools', 'rank-math' ) }</li>
						</ul>
					</div>

					<div className="serp-preview-result-stats">
						{ __(
							'About 43,700,000 results (0.32 seconds) ',
							'rank-math'
						) }
					</div>
				</div>

				<div
					className="serp-preview-body"
					role="button"
					tabIndex={ 0 }
					onClick={ () => {
						if ( onClick ) {
							onClick()
						}
					} }
				>

					<div className="group">
						<div className="serp-preview-body-header">
							<div className="serp-preview-favicon">
								<img
									src={ rankMath.siteFavIcon }
									width="16"
									height="16"
									alt={ __( 'Site favicon', 'rank-math' ) }
								/>
							</div>
							<div>
								<span
									className="serp-blog-name"
									dangerouslySetInnerHTML={ { __html: Helpers.sanitizeText( rankMath.blogName ) } }
								></span>
								<div className="serp-url-items">
									<div
										className="serp-url"
										dangerouslySetInnerHTML={ {
											__html: highlight(
												keywordPermalink,
												Helpers.sanitizeText(
													`${ rankMath.homeUrl } › ${ __( rankMath.postName, 'rank-math' ) }`
												),
												75,
												/-? +/
											),
										} }
									></div>
									<div
										className="serp-url-suffix"
									>
										<svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path>
										</svg>
									</div>
								</div>

							</div>
						</div>
					</div>
					<div className="group">
						<h5
							className={ titleClasses }
							dangerouslySetInnerHTML={ {
								__html: highlight(
									keyword,
									Helpers.sanitizeText( title ),
									60
								),
							} }
						></h5>
					</div>

					<RatingPreview />

					<div className="group">
						<div
							className="serp-description"
							dangerouslySetInnerHTML={ {
								__html: highlight(
									keyword,
									Helpers.sanitizeText( description ),
									160
								),
							} }
						></div>
					</div>

				</div>

				<div className="serp-preview-noindex">
					<h3>
						{ __( 'Noindex robots meta is enabled', 'rank-math' ) }
					</h3>
					<p>
						{ __(
							'This page will not appear in search results. You can disable noindex in the Advanced tab.',
							'rank-math'
						) }
					</p>
				</div>
			</div>
		</div>
	)
}

export default withSelect( ( select ) => {
	const repo = select( 'rank-math' ),
		robots = repo.getRobots()

	return {
		title: repo.getSerpTitle(),
		permalink: rankMathEditor.assessor.dataCollector.getPermalink(),
		description: repo.getSerpDescription(),
		previewType: repo.getSnippetPreviewType(),
		isNoIndex: 'noindex' in robots,
		keyword: repo.getSelectedKeyword().data.value,
	}
} )( SerpPreview )
