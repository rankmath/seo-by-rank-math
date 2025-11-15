/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Notice from './Notice'

/**
 * Maybe show invalid site url notice component.
 *
 * @param {Object}  props                Component props
 * @param {boolean} props.isSiteUrlValid If true, site URL is valid.
 */
export default ( { isSiteUrlValid } ) => {
	if ( isSiteUrlValid ) {
		return
	}

	return (
		<Notice status="warning" className="notice-connect-disabled">
			<RawHTML>
				{ sprintf(
					// Translators: 1 is "WordPress Address (URL)", 2 is "Site Address (URL)", 3 is a link to the General Settings, with "WordPress General Settings" as anchor text.
					__(
						"Rank Math cannot be connected because your site URL doesn't appear to be a valid URL. If the domain name contains special characters, please make sure to use the encoded version in the %1$s &amp; %2$s fields on the %3$s page.",
						'rank-math'
					),
					`<strong>${ __( 'WordPress Address (URL)', 'rank-math' ) }</strong>`,
					`<strong>${ __( 'Site Address (URL)', 'rank-math' ) }</strong>`,
					`<a href="${ window.location.origin }/wp-admin/options-general.php" target="_blank">${ __(
						'WordPress General Settings',
						'rank-math'
					) }</a>`
				) }
			</RawHTML>
		</Notice>
	)
}
