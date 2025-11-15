/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Tab Header component.
 *
 * @param {Object} props             Component props.
 * @param {string} props.title       The page title.
 * @param {Object} props.description The tab description text.
 * @param {string} props.link        Learn more link destination.
 * @param {string} props.linkText    The link destination readable text.
 */
export default ( {
	title,
	description,
	link,
	linkText = __( 'Learn more', 'rank-math' ),
} ) => (
	<header className="rank-math-tab-header">
		<h2>{ title }</h2>
		<p>
			{ description }
			{ link && (
				<>
					{ ' ' }
					<a href={ link } target="_blank" rel="noreferrer">
						{ linkText }
					</a>
					.
				</>
			) }
		</p>
	</header>
)
