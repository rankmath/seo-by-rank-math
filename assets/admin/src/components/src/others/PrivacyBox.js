/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

/**
 * Privacy box component.
 *
 * @param {Object} props           Component props.
 * @param {string} props.className CSS class name for additional styling.
 */
export default ( { className = '', ...additionalProps } ) => (
	<div
		{ ...additionalProps }
		id="rank-math-pro-cta"
		className={ `rank-math-privacy-box ${ className }` }
	>
		<div className="rank-math-cta-table">
			<div className="rank-math-cta-body less-padding">
				<i className="dashicons dashicons-lock"></i>
				<p
					dangerouslySetInnerHTML={ {
						__html: sprintf(
							// Translators: placeholder is the KB link.
							__(
								'We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused. %s',
								'rank-math'
							),
							`<a href="${ getLink( 'usage-policy', 'Analytics Privacy Notice' ) }" target="_blank" rel="noopener noreferrer">${ __( 'Learn more.', 'rank-math' ) }</a>`
						),
					} }
				/>
			</div>
		</div>
	</div>
)
