/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import Notice from '../notice/Notice'

/**
 * Maybe show invalid site url notice component.
 *
 * @param {Object}  props                Component props
 * @param {boolean} props.isSiteUrlValid Determines if the site is a valid one.
 */
export default ( { isSiteUrlValid } ) => {
	if ( isSiteUrlValid ) {
		return
	}

	return (
		<Notice status="warning" className="notice notice-warning notice-alt notice-connect-disabled">
			<span
				dangerouslySetInnerHTML={ {
					__html: sprintf(
						// Translators: 1 is "WordPress Address (URL)", 2 is "Site Address (URL)", 3 is a link to the General Settings, with "WordPress General Settings" as anchor text.
						__(
							"Rank Math cannot be connected because your site URL doesn't appear to be a valid URL. If the domain name contains special characters, please make sure to use the encoded version in the %1$s &amp; %2$s fields on the %3$s page.",
							'rank-math'
						),
						`<strong>${ __( 'WordPress Address (URL)', 'rank-math' ) }</strong>`,
						`<strong>${ __( 'Site Address (URL)', 'rank-math' ) }</strong>`,
						`<a href="${ rankMath.optionsPage }">${ __( 'WordPress General Settings', 'rank-math' ) }</a>`
					),
				} }
			/>
		</Notice>
	)
}
