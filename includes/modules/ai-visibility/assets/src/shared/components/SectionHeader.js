/**
 * SectionHeader — page/section title + optional subtitle.
 *
 * @since 1.0.273
 */

/**
 * Internal dependencies
 */
import './SectionHeader.scss'

/**
 * SectionHeader component.
 *
 * @param {Object} props
 * @param {string} props.title      Section heading (required).
 * @param {string} [props.subtitle] Optional supporting text beneath the heading.
 * @return {JSX.Element|null} Section heading pair, or null when title is falsy.
 */
const SectionHeader = ( { title, subtitle } ) => {
	if ( ! title ) {
		return null
	}

	const ns = 'rank-math-ai-visibility-section-header'

	return (
		<div className={ ns }>
			<h2 className={ `${ ns }__title` }>
				{ title }
			</h2>
			{ subtitle && (
				<p className={ `${ ns }__subtitle` }>
					{ subtitle }
				</p>
			) }
		</div>
	)
}

export default SectionHeader
