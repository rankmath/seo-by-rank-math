/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Tab Header
 *
 * @param {Object} props             Component props.
 * @param {string} props.link        URL link for the tab.
 * @param {string} props.heading     Title of the tab.
 * @param {string} props.description Description of the tab.
 * @param {string} props.linkText    Text for the link. Defaults to 'Learn more.' if not provided.
 * @param {string} props.className   Class to add to the paragraph tag.
 */
export default ( {
	link,
	heading,
	description,
	linkText = __( 'Learn more.', 'rank-math' ),
	className = '',
} ) => (
	<header>
		<h1 dangerouslySetInnerHTML={ { __html: heading } } />
		<p className={ className }>
			{ description }
			{
				link &&
				<a href={ link } target="_blank" rel="noreferrer">
					{ linkText }
				</a>
			}
		</p>
	</header>
)
