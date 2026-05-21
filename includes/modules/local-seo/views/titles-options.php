<?php
/**
 * The local SEO settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Local_Seo
 */

use RankMath\Helper;
use RankMath\KB;

defined( 'ABSPATH' ) || exit;

$rank_math_company = [ [ 'knowledgegraph_type', 'company' ] ];
$rank_math_person  = [ [ 'knowledgegraph_type', 'person' ] ];

$cmb->add_field(
	[
		'id'      => 'knowledgegraph_type',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Person or Company', 'seo-by-rank-math' ),
		'options' => [
			'person'  => esc_html__( 'Person', 'seo-by-rank-math' ),
			'company' => esc_html__( 'Organization', 'seo-by-rank-math' ),
		],
		'desc'    => esc_html__( 'Choose whether the site represents a person or an organization.', 'seo-by-rank-math' ),
		'default' => 'person',
	]
);

$cmb->add_field(
	[
		'id'      => 'website_name',
		'type'    => 'text',
		'name'    => esc_html__( 'Website Name', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Enter the name of your site to appear in search results.', 'seo-by-rank-math' ),
		'default' => get_bloginfo( 'name' ),
	]
);

$cmb->add_field(
	[
		'id'   => 'website_alternate_name',
		'type' => 'text',
		'name' => esc_html__( 'Website Alternate Name', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'An alternate version of your site name (for example, an acronym or shorter name).', 'seo-by-rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'knowledgegraph_name',
		'type'    => 'text',
		'name'    => esc_html__( 'Person/Organization Name', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Your name or company name intended to feature in Google\'s Knowledge Panel.', 'seo-by-rank-math' ),
		'default' => get_bloginfo( 'name' ),
	]
);

$cmb->add_field(
	[
		'id'   => 'organization_description',
		'type' => 'textarea_small',
		'name' => esc_html__( 'Description', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'Provide a detailed description of your organization.', 'seo-by-rank-math' ),
		'dep'  => $rank_math_company,
	]
);

$cmb->add_field(
	[
		'id'      => 'knowledgegraph_logo',
		'type'    => 'file',
		'name'    => esc_html__( 'Logo', 'seo-by-rank-math' ),
		'desc'    => __( '<strong>Min Size: 112Χ112px</strong>.<br /> A squared image is preferred by the search engines.', 'seo-by-rank-math' ),
		'options' => [ 'url' => false ],
	]
);

$cmb->add_field(
	[
		'id'      => 'url',
		'type'    => 'text_url',
		'name'    => esc_html__( 'URL', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'URL of your website or your company’s website.', 'seo-by-rank-math' ),
		'default' => home_url(),
	]
);

$cmb->add_field(
	[
		'id'   => 'email',
		'type' => 'text',
		'name' => esc_html__( 'Email', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'Enter the contact email address that could be displayed on search engines.', 'seo-by-rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'   => 'phone',
		'type' => 'text',
		'name' => esc_html__( 'Phone', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'Search engines may prominently display your contact phone number for mobile users.', 'seo-by-rank-math' ),
		'dep'  => $rank_math_person,
	]
);

$cmb->add_field(
	[
		'id'   => 'local_address',
		'type' => 'address',
		'name' => esc_html__( 'Address', 'seo-by-rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'         => 'local_address_format',
		'type'       => 'textarea_small',
		'name'       => esc_html__( 'Address Format', 'seo-by-rank-math' ),
		'desc'       => wp_kses_post( __( 'Format used when the address is displayed using the <code>[rank_math_contact_info]</code> shortcode.<br><strong>Available Tags: {address}, {locality}, {region}, {postalcode}, {country}, {gps}</strong>', 'seo-by-rank-math' ) ),
		'default'    => '{address} {locality}, {region} {postalcode}',
		'classes'    => 'rank-math-address-format',
		'attributes' => [
			'rows'        => 2,
			'placeholder' => '{address} {locality}, {region} {country}. {postalcode}.',
		],
		'dep'        => $rank_math_company,
	]
);

$cmb->add_field(
	[
		'id'         => 'local_business_type',
		'type'       => 'select',
		'name'       => esc_html__( 'Business Type', 'seo-by-rank-math' ),
		'options'    => Helper::choices_business_types( true ),
		'attributes' => ( 'data-s2' ),
		'dep'        => $rank_math_company,
	]
);

$cmb->add_field(
	[
		'id'      => 'opening_hours_format',
		'type'    => 'switch',
		'name'    => esc_html__( 'Opening Hours Format', 'seo-by-rank-math' ),
		'options' => [
			'off' => '24:00',
			'on'  => '12:00',
		],
		'desc'    => esc_html__( 'Time format used in the contact shortcode.', 'seo-by-rank-math' ),
		'default' => 'off',
		'dep'     => $rank_math_company,
	]
);

$rank_math_opening_hours = $cmb->add_field(
	[
		'id'      => 'opening_hours',
		'type'    => 'group',
		'name'    => esc_html__( 'Opening Hours', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Select opening hours. You can add multiple sets if you have different opening or closing hours on some days or if you have a mid-day break. Times are specified using 24:00 time.', 'seo-by-rank-math' ),
		'options' => [
			'add_button'    => esc_html__( 'Add time', 'seo-by-rank-math' ),
			'remove_button' => esc_html__( 'Remove', 'seo-by-rank-math' ),
		],
		'dep'     => $rank_math_company,
		'classes' => 'cmb-group-text-only',
	]
);

$cmb->add_group_field(
	$rank_math_opening_hours,
	[
		'id'      => 'day',
		'type'    => 'select',
		'options' => [
			'Monday'    => esc_html__( 'Monday', 'seo-by-rank-math' ),
			'Tuesday'   => esc_html__( 'Tuesday', 'seo-by-rank-math' ),
			'Wednesday' => esc_html__( 'Wednesday', 'seo-by-rank-math' ),
			'Thursday'  => esc_html__( 'Thursday', 'seo-by-rank-math' ),
			'Friday'    => esc_html__( 'Friday', 'seo-by-rank-math' ),
			'Saturday'  => esc_html__( 'Saturday', 'seo-by-rank-math' ),
			'Sunday'    => esc_html__( 'Sunday', 'seo-by-rank-math' ),
		],
	]
);

$cmb->add_group_field(
	$rank_math_opening_hours,
	[
		'id'          => 'time',
		'type'        => 'text',
		'attributes'  => [ 'placeholder' => esc_html__( 'e.g. 09:00-17:00', 'seo-by-rank-math' ) ],
		'time_format' => 'H:i',
	]
);

$rank_math_phones = $cmb->add_field(
	[
		'id'      => 'phone_numbers',
		'type'    => 'group',
		'name'    => esc_html__( 'Phone Number', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Search engines may prominently display your contact phone number for mobile users.', 'seo-by-rank-math' ),
		'options' => [
			'add_button'    => esc_html__( 'Add number', 'seo-by-rank-math' ),
			'remove_button' => esc_html__( 'Remove', 'seo-by-rank-math' ),
		],
		'dep'     => $rank_math_company,
		'classes' => 'cmb-group-text-only',
	]
);

$cmb->add_group_field(
	$rank_math_phones,
	[
		'id'      => 'type',
		'type'    => 'select',
		'options' => Helper::choices_phone_types(),
		'default' => 'customer_support',
	]
);

$cmb->add_group_field(
	$rank_math_phones,
	[
		'id'         => 'number',
		'type'       => 'text',
		'attributes' => [ 'placeholder' => esc_html__( 'Format: +1-401-555-1212', 'seo-by-rank-math' ) ],
	]
);

$cmb->add_field(
	[
		'id'   => 'price_range',
		'type' => 'text',
		'name' => esc_html__( 'Price Range', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'The price range of the business, for example $$$.', 'seo-by-rank-math' ),
		'dep'  => $rank_math_company,
	]
);

$rank_math_additional_info = $cmb->add_field(
	[
		'id'      => 'additional_info',
		'type'    => 'group',
		'name'    => esc_html__( 'Additional Info', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Provide relevant details of your company to include in the Organization Schema.', 'seo-by-rank-math' ),
		'options' => [
			'add_button'    => esc_html__( 'Add', 'seo-by-rank-math' ),
			'remove_button' => esc_html__( 'Remove', 'seo-by-rank-math' ),
		],
		'dep'     => $rank_math_company,
		'classes' => 'cmb-group-text-only',
	]
);

$cmb->add_group_field(
	$rank_math_additional_info,
	[
		'id'      => 'type',
		'type'    => 'select',
		'options' => Helper::choices_additional_organization_info(),
		'default' => '',
	]
);

$cmb->add_group_field(
	$rank_math_additional_info,
	[
		'id'   => 'value',
		'type' => 'text',
	]
);

$rank_math_about_page    = Helper::get_settings( 'titles.local_seo_about_page' );
$rank_math_about_options = [ '' => __( 'Select Page', 'seo-by-rank-math' ) ];

if ( $rank_math_about_page ) {
	$rank_math_about_options[ $rank_math_about_page ] = get_the_title( $rank_math_about_page );
}

$cmb->add_field(
	[
		'id'         => 'local_seo_about_page',
		'type'       => 'select',
		'options'    => $rank_math_about_options,
		'name'       => esc_html__( 'About Page', 'seo-by-rank-math' ),
		'desc'       => esc_html__( 'Select a page on your site where you want to show the LocalBusiness meta data.', 'seo-by-rank-math' ),
		'attributes' => ( 'data-s2-pages' ),
	]
);

$rank_math_contact_page    = Helper::get_settings( 'titles.local_seo_contact_page' );
$rank_math_contact_options = [ '' => __( 'Select Page', 'seo-by-rank-math' ) ];

if ( $rank_math_contact_page ) {
	$rank_math_contact_options[ $rank_math_contact_page ] = get_the_title( $rank_math_contact_page );
}

$cmb->add_field(
	[
		'id'         => 'local_seo_contact_page',
		'type'       => 'select',
		'options'    => $rank_math_contact_options,
		'name'       => esc_html__( 'Contact Page', 'seo-by-rank-math' ),
		'desc'       => esc_html__( 'Select a page on your site where you want to show the LocalBusiness meta data.', 'seo-by-rank-math' ),
		'attributes' => ( 'data-s2-pages' ),
	]
);

$cmb->add_field(
	[
		'id'         => 'maps_api_key',
		'type'       => 'text',
		'name'       => esc_html__( 'Google Maps API Key', 'seo-by-rank-math' ),
		/* translators: %s expands to "Google Maps Embed API" https://developers.google.com/maps/documentation/embed/ */
		'desc'       => sprintf( esc_html__( 'An API Key is required to display embedded Google Maps on your site. Get it here: %s', 'seo-by-rank-math' ), '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">' . __( 'Google Maps Embed API', 'seo-by-rank-math' ) . '</a>' ),
		'dep'        => $rank_math_company,
		'attributes' => [ 'type' => 'password' ],
	]
);

$cmb->add_field(
	[
		'id'    => 'geo',
		'type'  => 'text',
		'name'  => esc_html__( 'Geo Coordinates', 'seo-by-rank-math' ),
		'desc'  => esc_html__( 'Latitude and longitude values separated by comma.', 'seo-by-rank-math' ),
		'dep'   => $rank_math_company,
		/* Translators: placeholder is a link to the Pro version */
		'after' => '<strong style="margin-top:20px; display:block; text-align:right;">' . sprintf( __( 'Multiple Locations are available in the %s.', 'seo-by-rank-math' ), '<a href="' . KB::get( 'pro', 'Multiple Location Notice' ) . '" target="_blank">PRO Version</a>' ) . '</strong>',
	]
);
