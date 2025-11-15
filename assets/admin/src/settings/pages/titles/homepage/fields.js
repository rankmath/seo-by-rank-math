/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'

/**
 * Internal dependencies
 */
import choicesRobots from '../../../helpers/choicesRobots'
import AdvancedRobots from '../../../components/AdvancedRobots'

const notice = [
	{
		id: 'static_homepage_notice',
		type: 'notice',
		status: 'warning',
		children: <RawHTML>
			{
				__(
					'Static page is set as the front page (WP Dashboard > Settings > Reading). To add SEO title, description, and meta for the homepage, please click here: ',
					'rank-math'
				)
			}
			{ rankMath.staticHomePageNotice }
		</RawHTML>,
	},
]

const fields = [
	{
		id: 'homepage_title',
		type: 'selectVariable',
		name: __( 'Homepage Title', 'rank-math' ),
		desc: __( 'Homepage title tag.', 'rank-math' ),
		classes: 'rank-math-supports-variables rank-math-title',
		exclude: [ 'seo_title', 'seo_description' ],
		default: '%sitename% %page% %sep% %sitedesc%',
	},
	{
		id: 'homepage_description',
		type: 'selectVariable',
		as: 'textarea',
		name: __( 'Homepage Meta Description', 'rank-math' ),
		desc: __( 'Homepage meta description.', 'rank-math' ),
		classes: 'rank-math-supports-variables rank-math-description',
		exclude: [ 'seo_title', 'seo_description' ],
	},
	{
		id: 'homepage_custom_robots',
		type: 'toggle',
		name: __( 'Homepage Robots Meta', 'rank-math' ),
		desc: __(
			'Select custom robots meta for homepage, such as <code>nofollow</code>, <code>noarchive</code>, etc. Otherwise the default meta will be used, as set in the Global Meta tab.',
			'rank-math'
		),
		default: false,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'homepage_robots',
		type: 'checkboxlist',
		name: __( 'Homepage Robots Meta', 'rank-math' ),
		desc: __( 'Custom values for robots meta tag on homepage.', 'rank-math' ),
		options: choicesRobots,
		dep: {
			homepage_custom_robots: true,
		},
		default: [ 'index' ],
		classes: 'rank-math-advanced-option rank-math-robots-data',
	},
	{
		id: 'homepage_advanced_robots',
		type: 'component',
		Component: AdvancedRobots,
		name: __( 'Homepage Advanced Robots', 'rank-math' ),
		classes: 'rank-math-advanced-option rank-math-advanced-robots-field',
		dep: {
			homepage_custom_robots: true,
		},
		default: {
			'max-snippet': -1,
			'max-video-preview': -1,
			'max-image-preview': 'large',
		},
	},
	{
		id: 'homepage_facebook_title',
		type: 'text',
		name: __( 'Homepage Title for Facebook', 'rank-math' ),
		desc: __(
			'Title of your site when shared on Facebook, Twitter and other social networks.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'homepage_facebook_description',
		type: 'textarea',
		name: __( 'Homepage Description for Facebook', 'rank-math' ),
		desc: __(
			'Description of your site when shared on Facebook, Twitter and other social networks.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'homepage_facebook_image',
		type: 'file',
		name: __( 'Homepage Thumbnail for Facebook', 'rank-math' ),
		desc: __(
			'Image displayed when your homepage is shared on Facebook and other social networks. Use images that are at least 1200 x 630 pixels for the best display on high resolution devices.',
			'rank-math'
		),
	},
]

export default rankMath.staticHomePageNotice ? notice : fields
