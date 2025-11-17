/**
 * External dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { RawHTML } from '@wordpress/element'

/**
 * Internal dependencies
 */
import getAddressFields from './getAddressFields'
import getOpeningHoursFields from './getOpeningHoursFields'
import getPhoneNumberFields from './getPhoneNumberFields'
import getAdditionaInfoFields from './getAdditionaInfoFields'
import getLink from '@helpers/getLink'

const isLocalSeoActive = includes( rankMath.modules, 'local-seo' )
const companyDep = { knowledgegraph_type: 'company' }
const personDep = { knowledgegraph_type: 'person' }

const repeatableGroupOptions = ( buttonText ) => {
	return {
		addButton: {
			children: buttonText,
		},
		removeButton: {
			children: __( 'Remove', 'rank-math' ),
		},
	}
}

const getFields = () => {
	const fields = [
		{
			type: 'notice',
			status: 'info',
			children: (
				<RawHTML>
					{ __(
						'Use the <code>[rank_math_contact_info]</code> shortcode to display contact information in a nicely formatted way. You should also claim your business on Google if you have not already.',
						'rank-math'
					) }
				</RawHTML>
			),
		},
		{
			id: 'knowledgegraph_type',
			type: 'toggleGroup',
			name: __( 'Person or Company', 'rank-math' ),
			desc: __(
				'Choose whether the site represents a person or an organization.',
				'rank-math'
			),
			options: {
				person: __( 'Person', 'rank-math' ),
				company: __( 'Organization', 'rank-math' ),
			},
			default: 'person',
		},
		{
			id: 'website_name',
			type: 'text',
			name: __( 'Website Name', 'rank-math' ),
			desc: __(
				'Enter the name of your site to appear in search results.',
				'rank-math'
			),
			default: rankMath.blogName,
		},
		{
			id: 'website_alternate_name',
			type: 'text',
			name: __( 'Website Alternate Name', 'rank-math' ),
			desc: __(
				'An alternate version of your site name (for example, an acronym or shorter name).',
				'rank-math'
			),
		},
		{
			id: 'knowledgegraph_name',
			type: 'text',
			name: __( 'Person/Organization Name', 'rank-math' ),
			desc: __(
				"Your name or company name intended to feature in Google's Knowledge Panel.",
				'rank-math'
			),
			default: rankMath.blogName,
		},
		...( isLocalSeoActive
			? [
				{
					id: 'organization_description',
					type: 'textarea',
					name: __( 'Description', 'rank-math' ),
					desc: __(
						'Provide a detailed description of your organization.',
						'rank-math'
					),
					dep: companyDep,
				},
			]
			: []
		),
		{
			id: 'knowledgegraph_logo',
			type: 'file',
			name: __( 'Logo', 'rank-math' ),
			desc: __(
				'<strong>Min Size: 112Χ112px</strong>.<br /> A squared image is preferred by the search engines.',
				'rank-math'
			),
		},
		{
			id: 'url',
			type: 'text',
			name: __( 'URL', 'rank-math' ),
			desc: __( 'URL of the item.', 'rank-math' ),
			default: rankMath.homeUrl,
		},
	]

	if ( ! isLocalSeoActive ) {
		return fields
	}

	const businessTypes = rankMath.businessTypes
	businessTypes[ '' ] = __( 'None', 'rank-math' )

	const localSeoFields = [
		{
			id: 'email',
			type: 'text',
			name: __( 'Email', 'rank-math' ),
			desc: __( 'Enter the contact email address that could be displayed on search engines.', 'rank-math' ),
		},
		{
			id: 'phone',
			type: 'text',
			name: __( 'Phone', 'rank-math' ),
			desc: __( 'Search engines may prominently display your contact phone number for mobile users.', 'rank-math' ),
			dep: personDep,
		},
		{
			id: 'local_address',
			type: 'group',
			fields: getAddressFields( 'local_address' ),
			name: __( 'Address', 'rank-math' ),
		},
		{
			id: 'local_address_format',
			type: 'textarea',
			name: __( 'Address Format', 'rank-math' ),
			desc: __( 'Format used when the address is displayed using the <code>[rank_math_contact_info]</code> shortcode.<br><strong>Available Tags: {address}, {locality}, {region}, {postalcode}, {country}, {gps}</strong>', 'rank-math' ),
			classes: 'rank-math-address-format',
			default: '{address} {locality}, {region} {postalcode}',
			placeholder: '{address} {locality}, {region} {country}. {postalcode}.',
			dep: companyDep,
		},
		{
			id: 'local_business_type',
			type: 'selectSearch',
			name: __( 'Business Type', 'rank-math' ),
			options: businessTypes,
			dep: companyDep,
			default: '',
		},
		{
			id: 'opening_hours',
			type: 'repeatableGroup',
			name: __( 'Opening Hours', 'rank-math-pro' ),
			desc: __(
				'Select opening hours. You can add multiple sets if you have different opening or closing hours on some days or if you have a mid-day break. Times are specified using 24:00 time.',
				'rank-math-pro'
			),
			options: repeatableGroupOptions( __( 'Add time', 'rank-math' ) ),
			fields: getOpeningHoursFields(),
			classes: 'field-group-text-only',
			dep: companyDep,
			default: [
				{
					day: 'Monday',
					time: '',
				},
			],
		},
		{
			id: 'opening_hours_format',
			type: 'toggleGroup',
			name: __( 'Opening Hours Format', 'rank-math' ),
			desc: __(
				'Time format used in the contact shortcode.',
				'rank-math'
			),
			options: {
				off: '24:00',
				on: '12:00',
			},
			default: 'off',
			dep: companyDep,
		},
		{
			id: 'phone_numbers',
			type: 'repeatableGroup',
			name: __( 'Phone Number', 'rank-math-pro' ),
			desc: __(
				'Search engines may prominently display your contact phone number for mobile users.',
				'rank-math-pro'
			),
			options: repeatableGroupOptions( __( 'Add Number', 'rank-math' ) ),
			fields: getPhoneNumberFields(),
			classes: 'field-group-text-only',
			dep: companyDep,
			default: [
				{
					type: 'customer support',
					number: '',
				},
			],
		},
		{
			id: 'price_range',
			type: 'text',
			name: __( 'Price Range', 'rank-math' ),
			desc: __( 'The price range of the business, for example $$$.', 'rank-math' ),
			dep: companyDep,
		},
		{
			id: 'additional_info',
			type: 'repeatableGroup',
			name: __( 'Additional Info', 'rank-math-pro' ),
			desc: __(
				'Provide relevant details of your company to include in the Organization Schema.',
				'rank-math-pro'
			),
			options: repeatableGroupOptions( __( 'Add', 'rank-math' ) ),
			fields: getAdditionaInfoFields(),
			classes: 'field-group-text-only',
			dep: companyDep,
			default: [
				{
					type: 'legalName',
					value: '',
				},
			],
		},
		{
			id: 'maps_api_key',
			type: 'text',
			name: __( 'Google Maps API Key', 'rank-math' ),
			/* translators: %s expands to "Google Maps Embed API" https://developers.google.com/maps/documentation/embed/ */
			desc: sprintf( __( 'An API Key is required to display embedded Google Maps on your site. Get it here: %s', 'rank-math' ), '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">' + __( 'Google Maps Embed API', 'rank-math' ) + '</a>' ),
			dep: companyDep,
			attributes: {
				type: 'password',
			},
		},
		{
			id: 'geo',
			type: 'text',
			name: __( 'Geo Coordinates', 'rank-math' ),
			desc: __( 'Latitude and longitude values separated by comma.', 'rank-math' ),
			dep: companyDep,
		},
		{
			id: 'local_seo_about_page',
			type: 'searchPage',
			selectedPage: rankMath.aboutPage,
			name: __( 'About Page', 'rank-math' ),
			desc: __( 'Select a page on your site where you want to show the LocalBusiness meta data.', 'rank-math' ),
		},
		{
			id: 'local_seo_contact_page',
			type: 'searchPage',
			selectedPage: rankMath.contactPage,
			name: __( 'Contact Page', 'rank-math' ),
			desc: __( 'Select a page on your site where you want to show the LocalBusiness meta data.', 'rank-math' ),
		},
		...( ! rankMath.isPro
			? [
				{
					type: 'notice',
					status: 'message',
					children: (
						<RawHTML>
							{
								'<strong style="margin-top:20px; display:block; text-align:right;">' +
								sprintf(
									/* Translators: placeholder is a link to the Pro version */
									__( 'Multiple Locations are available in the %s.', 'rank-math' ),
									'<a href="' + getLink( 'pro', 'Multiple Location Notice' ) + '" target="_blank">PRO Version</a>'
								) +
								'</strong>'
							}
						</RawHTML>
					),
				},
			]
			: []
		),
	]

	fields.push( ...localSeoFields )

	return fields
}

export default getFields()
