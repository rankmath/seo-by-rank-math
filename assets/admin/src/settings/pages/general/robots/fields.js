/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'

const { isEditAllowed, robotsData = {} } = rankMath

/**
 * Get notice field for either robots locked or site not public
 */
const getNoticeField = () => {
	let desc = ''

	if ( robotsData.exists ) {
		desc = __(
			'Contents are locked because a robots.txt file is present in the root folder.',
			'rank-math'
		)
	}

	if ( ! isUndefined( robotsData.writable ) && robotsData.writable === false ) {
		desc = __(
			'Rank Math could not detect if a robots.txt file exists or not because of a filesystem issue. The file contents entered here may not be applied.',
			'rank-math'
		)
	}

	if ( desc ) {
		return [
			{
				id: 'robots_locked',
				type: 'notice',
				status: 'warning',
				classes: 'nob nopt rank-math-notice-field',
				children: desc,
			},
		]
	} else if ( 0 === robotsData.public ) {
		return [
			{
				id: 'site_not_public',
				type: 'notice',
				status: 'warning',
				classes: 'nob nopt rank-math-notice-field',
				children: (
					<RawHTML>
						{ sprintf(
							// Translators: placeholder is the Settings page URL.
							__(
								'<strong>Warning:</strong> your site\'s search engine visibility is set to Hidden in <a href="%1$s" target="_blank">Settings &gt; Reading</a>. This means that the changes you make here will not take effect. Set the search engine visibility to Public to be able to change the robots.txt content.',
								'rank-math'
							),
							rankMath.adminurl + 'options-reading.php'
						) }
					</RawHTML>
				),
			},
		]
	}

	return []
}

export default [
	{
		type: 'raw',
		content: (
			<div key="robots-description" className="rank-math-desc">
				{ __(
					"Leave the field empty to let WordPress handle the contents dynamically. If an actual robots.txt file is present in the root folder of your site, this option won't take effect and you have to edit the file directly, or delete it and then edit from here.",
					'rank-math'
				) }
			</div>
		),
	},
	...( ! isEditAllowed
		? [
			{
				id: 'edit_disabled',
				type: 'notice',
				status: 'error',
				children: __( 'robots.txt file is not writable.', 'rank-math' ),
			},
		]
		: []
	),
	{
		id: 'robots_txt_content',
		type: 'textarea',
		attributes: {
			'data-gramm': false,
			readOnly: robotsData.exists || robotsData.public === 0,
			placeholder: ! robotsData.exists || ! robotsData.writable ? robotsData.default : undefined,
			disabled: ! isEditAllowed,
			variant: 'code-box',
			rows: 10,
		},
		classes: 'nob rank-math-code-box',
	},
	...getNoticeField(),
	{
		id: 'robots_tester',
		type: 'notice',
		status: 'info',
		classes: 'nob nopt rank-math-notice-field',
		children: (
			<RawHTML>
				{ sprintf(
					// Translators: placeholder is the URL to the robots.txt tester tool.
					__( 'Test and edit your live robots.txt file with our %1$s.', 'rank-math' ),
					'<a href="' + getLink( 'robotstxt-tool', 'Options Panel Robots.txt Tester' ) + `&url=${ window.location.origin }` + '">' + __( 'Robots.txt Tester', 'rank-math' ) + '</a>'
				) }
			</RawHTML>
		),
	},
]
