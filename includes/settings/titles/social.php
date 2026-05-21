<?php
/**
 * The social settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

defined( 'ABSPATH' ) || exit;

use RankMath\KB;

$cmb->add_field(
	[
		'id'   => 'social_url_facebook',
		'type' => 'text_url',
		'name' => esc_html__( 'Facebook Page URL', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'Enter your complete Facebook page URL here. eg:', 'seo-by-rank-math' ) .
			'<br><code>' . htmlspecialchars( 'https://www.facebook.com/RankMath/' ) . '</code>',
	]
);

$cmb->add_field(
	[
		'id'   => 'facebook_author_urls',
		'type' => 'text_url',
		'name' => esc_html__( 'Facebook Authorship', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'Insert personal Facebook profile URL to show Facebook Authorship when your articles are being shared on Facebook. eg:', 'seo-by-rank-math' ) .
			'<br><code>' . htmlspecialchars( 'https://www.facebook.com/zuck' ) . '</code>',
	]
);

$cmb->add_field(
	[
		'id'   => 'facebook_admin_id',
		'type' => 'text',
		'name' => esc_html__( 'Facebook Admin', 'seo-by-rank-math' ),
		/* translators: numeric user ID link */
		'desc' => sprintf( esc_html__( 'Enter %s. Use a comma to separate multiple IDs. Alternatively, you can enter an app ID below.', 'seo-by-rank-math' ), '<a href="https://lookup-id.com/?utm_campaign=Rank+Math" target="_blank">numeric user ID</a>' ),
	]
);

$cmb->add_field(
	[
		'id'   => 'facebook_app_id',
		'type' => 'text',
		'name' => esc_html__( 'Facebook App', 'seo-by-rank-math' ),
		/* translators: numeric app ID link */
		'desc' => sprintf( esc_html__( 'Enter %s. Alternatively, you can enter a user ID above.', 'seo-by-rank-math' ), '<a href="https://developers.facebook.com/apps?utm_campaign=Rank+Math" target="_blank">numeric app ID</a>' ),
	]
);

$cmb->add_field(
	[
		'id'         => 'facebook_secret',
		'type'       => 'text',
		'name'       => esc_html__( 'Facebook Secret', 'seo-by-rank-math' ),
		/* translators: Learn more link */
		'desc'       => sprintf( esc_html__( 'Enter alphanumeric secret ID. %s.', 'seo-by-rank-math' ), '<a href="' . KB::get( 'create-facebook-app' ) . '" target="_blank">Learn more</a>' ),
		'attributes' => [ 'type' => 'password' ],
	]
);

$cmb->add_field(
	[
		'id'   => 'social_url_facebook',
		'type' => 'text_url',
		'name' => esc_html__( 'Facebook Page URL', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'Enter your complete Facebook page URL here. eg:', 'seo-by-rank-math' ) .
			'<br><code>' . htmlspecialchars( 'https://www.facebook.com/RankMath/' ) . '</code>',
	]
);

$cmb->add_field(
	[
		'id'   => 'twitter_author_names',
		'type' => 'text',
		'name' => esc_html__( 'Twitter Username', 'seo-by-rank-math' ),
		'desc' => wp_kses_post( __( 'Enter the Twitter username of the author to add <code>twitter:creator</code> tag to posts. eg: <code>RankMathSEO</code>', 'seo-by-rank-math' ) ),
	]
);

$cmb->add_field(
	[
		'id'   => 'social_additional_profiles',
		'type' => 'textarea_small',
		'name' => esc_html__( 'Additional Profiles', 'seo-by-rank-math' ),
		'desc' => wp_kses_post( __( 'Additional Profiles to add in the <code>sameAs</code> Schema property.', 'seo-by-rank-math' ) ),
	]
);
