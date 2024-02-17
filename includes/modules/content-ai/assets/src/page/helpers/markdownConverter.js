/**
 * External dependencies
 */
import showdown from 'showdown'
import { isString } from 'lodash'

/**
 * WordPress dependencies
 */
import { rawHandler, serialize } from '@wordpress/blocks'

// Reuse the same showdown converter.
const converter = new showdown.Converter( {
	noHeaderId: true,
	tables: true,
	literalMidWordUnderscores: true,
	omitExtraWLInCodeBlocks: true,
	simpleLineBreaks: true,
	strikethrough: true,
} )

/**
 * Corrects the Slack Markdown variant of the code block.
 * If uncorrected, it will be converted to inline code.
 *
 * @see https://get.slack.help/hc/en-us/articles/202288908-how-can-i-add-formatting-to-my-messages-#code-blocks
 *
 * @param {string} text The potential Markdown text to correct.
 *
 * @return {string} The corrected Markdown.
 */
const slackMarkdownVariantCorrector = ( text ) => {
	return text.replace(
		/((?:^|\n)```)([^\n`]+)(```(?:$|\n))/,
		( match, p1, p2, p3 ) => `${ p1 }\n${ p2 }\n${ p3 }`
	);
}

const bulletsToAsterisks = ( text ) => {
	return text.replace( /(^|\n)•( +)/g, '$1*$2' )
}

/**
 * Converts a piece of text into HTML based on any Markdown present.
 * Also decodes any encoded HTML.
 *
 * @param {string} text         The plain text to convert.
 * @param {string} makeMarkdown Convert content to markdown format.
 *
 * @return {string} HTML.
 */
export default ( text, makeMarkdown = false ) => {
	if ( ! isString( text ) ) {
		return text
	}

	if ( makeMarkdown ) {
		return converter.makeMarkdown( text )
	}

	if ( ! rankMath.isContentAIPage && rankMath.currentEditor !== 'gutenberg' ) {
		return converter.makeHtml(
			slackMarkdownVariantCorrector( bulletsToAsterisks( text ) )
		)
	}

	return serialize(
		rawHandler( {
			HTML: converter.makeHtml(
				slackMarkdownVariantCorrector( bulletsToAsterisks( text ) )
			),
			mode: 'BLOCKS',
		} )
	)
}
