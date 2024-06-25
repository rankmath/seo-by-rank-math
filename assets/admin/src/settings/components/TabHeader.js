/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import '../scss/TabHeader.scss'

/**
 * Tab Header component.
 *
 * @param {Object} props             Component props.
 * @param {string} props.title       The page title.
 * @param {Object} props.description The tab description text.
 * @param {string} props.link        Learn more link destination.
 */
export default ( { title, description, link } ) => (
	<header className="rank-math-tab-header">
		<h2>{ title }</h2>
		<p>
			{ description }
			{ link && (
				<>
					{ ' ' }
					<a href={ link } target="_blank" rel="noreferrer">
						{ __( 'Learn more', 'rank-math' ) }
					</a>.
				</>
			) }
		</p>
	</header>
)
