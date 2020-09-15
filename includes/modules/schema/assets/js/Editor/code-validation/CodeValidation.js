/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Fragment } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { prettyJSON } from '@schema/functions'

/**
 * Code validation component.
 */
const CodeValidation = () => {
	let previewData = {
		'@context': 'https://schema.org/',
		'@graph': [
			{
				'@type': 'Article',
				headline: 'Power Words: The Art of Writing Headlines That Get Clicked',
				description: 'Power words are words with strong meaning that smart copywriters (as well as marketers) use to increase CTR and boost conversions.',
				author: {
					'@type': 'Person',
					name: 'Rank Math',
				},
				datePublished: '2020-09-12GMT+000015:45:32+00:00',
				dateModified: '2020-09-12GMT+000015:45:29+00:00',
				'@id': 'https://rankmath.com/blog/power-words/#schema-44838',
				mainEntityOfPage: {
					'@id': 'https://rankmath.com/blog/power-words/#webpage',
				},
				isPartOf: {
					'@id': 'https://rankmath.com/blog/power-words/#webpage',
				},
				publisher: {
					'@id': '/#organization',
				},
				inLanguage: 'en-US',
			},
		],
	}
	previewData = JSON.stringify( previewData, null, 2 )

	return (
		<Fragment>
			<div className="rank-math-pretty-json free-version">
				<form method="post" target="_blank" action="https://search.google.com/test/rich-results">
					<h4 className="rank-math-schema-section-title">{ __( 'JSON-LD Code', 'rank-math' ) }</h4>
					<button className="button structured-data-copy is-small" type="button" data-clipboard-text={ previewData }>
						<i className="rm-icon rm-icon-copy"></i>
						<span className="original-text">{ __( 'Copy', 'rank-math' ) }</span>
						<span className="success" aria-hidden="true">{ __( 'Copied!', 'rank-math' ) }</span>
					</button>
					<button className="button structured-data-test is-small" type="submit">
						<i className="rm-icon rm-icon-google"></i> <span>{ __( 'Test with Google', 'rank-math' ) }</span>
					</button>
					<textarea name="code_snippet" defaultValue={ previewData } />
				</form>
				<pre className="code-output">
					<code className="language-javascript" dangerouslySetInnerHTML={ { __html: prettyJSON( previewData ) } } />
				</pre>
				<div className="rank-math-pro-cta center">
					<div className="rank-math-cta-box">
						<h3>{ __( 'Preview & Test Schema Code', 'rank-math' ) }</h3>
						<ul>
							<li>{ __( 'Advanced Schema code viewer', 'rank-math' ) }</li>
							<li>{ __( "Test on Google's Rich Result testing tool", 'rank-math' ) }</li>
						</ul>
						<button className="button button-primary">{ __( 'Pro Coming Soon', 'rank-math' ) }</button>
					</div>
				</div>
			</div>
		</Fragment>
	)
}

export default CodeValidation
