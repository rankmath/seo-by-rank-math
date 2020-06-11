<?php
/**
 * Metabox - Social Tab
 *
 * @package    RankMath
 * @subpackage RankMath\Metaboxes
 */

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

$cmb->add_field(
	[
		'id'   => 'rank_math_social_tabs',
		'type' => 'raw',
		'file' => rank_math()->includes_dir() . 'metaboxes/social-preview.php',
	]
);

/**
 * Facebook data.
 */
$cmb->add_field(
	[
		'name'    => esc_html__( 'Panel', 'rank-math' ),
		'id'      => 'setting-panel-social-tab-content-start',
		'type'    => 'raw',
		'content' => '<div class="rank-math-tabs-content rank-math-custom">',
	]
);

$cmb->add_field(
	[
		'name' => esc_html__( 'Panel', 'rank-math' ),
		'id'   => 'setting-panel-social-facebook',
		'type' => 'tab',
		'open' => true,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_facebook_image',
		'type'    => 'file',
		'name'    => esc_html__( 'Image', 'rank-math' ),
		'options' => [ 'url' => false ],
		'text'    => [ 'add_upload_file_text' => esc_html__( 'Add Image', 'rank-math' ) ],
		'desc'    => esc_html__( 'Upload at least 600x315px image. Recommended size is 1200x630px.', 'rank-math' ),
		'after'   => '<div class="notice notice-warning inline hidden rank-math-notice"><p>' . esc_html__( 'Image is smaller than the minimum size, please select a different image.', 'rank-math' ) . '</p></div>',
	]
);

$cmb->add_field(
	[
		'id'   => 'rank_math_facebook_title',
		'type' => 'text',
		'name' => esc_html__( 'Title', 'rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_facebook_description',
		'type'       => 'textarea',
		'name'       => esc_html__( 'Description', 'rank-math' ),
		'attributes' => [
			'rows'            => 3,
			'data-autoresize' => true,
		],
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_facebook_enable_image_overlay',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Add icon overlay to thumbnail', 'rank-math' ),
		'desc'    => wp_kses_post( __( '<div class="notice notice-alt notice-warning warning inline rank-math-notice"><p>Please be careful with this option. Although this option will help increase CTR on Facebook, it might get you penalised if over-used.</p></div>', 'rank-math' ) ),
		'default' => $this->do_filter( 'metabox/social/overlay_icon', 'off', 'facebook' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_facebook_image_overlay',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Icon overlay', 'rank-math' ),
		'options' => Helper::choices_overlay_images( 'names' ),
		'default' => 'play',
		'dep'     => [
			[ 'rank_math_facebook_enable_image_overlay', 'on' ],
		],
	]
);

if ( Admin_Helper::is_user_edit() ) {
	$cmb->add_field(
		[
			'id'   => 'rank_math_facebook_author',
			'type' => 'text',
			'name' => esc_html__( 'Author Profile URL', 'rank-math' ),
			/* translators: option page link */
			'desc' => sprintf( wp_kses_post( __( 'Insert a Facebook profile URL to display author name when the page is shared on Facebook.<br>The author name will be clickable if the profile is set to allow public followers.<br>You can set up default URL for fallback in <a href="%s" target="_blank">SEO &raquo; Titles &amp; Meta &raquo; Social</a>.', 'rank-math' ) ), Helper::get_admin_url( 'options-titles#setting-panel-social' ) ),
		]
	);
}

$cmb->add_field(
	[
		'id'   => 'setting-panel-social-facebook-close',
		'type' => 'tab',
	]
);

/**
 * Twitter data.
 */
$dep = [
	[ 'rank_math_twitter_use_facebook', 'on', '!=' ],
];

$cmb->add_field(
	[
		'name' => esc_html__( 'Panel', 'rank-math' ),
		'id'   => 'setting-panel-social-twitter',
		'type' => 'tab',
		'open' => true,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_twitter_use_facebook',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Use Data from Facebook Tab', 'rank-math' ),
		'default' => 'on',
	]
);

$card_type = [
	'summary_large_image' => esc_html__( 'Summary Card with Large Image', 'rank-math' ),
	'summary_card'        => esc_html__( 'Summary Card', 'rank-math' ),
	'app'                 => esc_html__( 'App Card', 'rank-math' ),
	'player'              => esc_html__( 'Player Card', 'rank-math' ),
];
if ( Admin_Helper::is_term_profile_page() ) {
	unset( $card_type['app'], $card_type['player'] );
}
$cmb->add_field(
	[
		'id'      => 'rank_math_twitter_card_type',
		'type'    => 'select',
		'name'    => esc_html__( 'Card type', 'rank-math' ),
		'options' => $card_type,
		'default' => Helper::get_settings( 'titles.twitter_card_type' ),
	]
);

if ( ! Admin_Helper::is_term_profile_page() ) {
	$player = [ [ 'rank_math_twitter_card_type', 'player' ] ];
	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_player_info',
			'type'    => 'notice',
			'what'    => 'info',
			/* translators: Link to twitter player card doc */
			'content' => sprintf( esc_html__( 'Video clips and audio streams have a special place on the Twitter platform thanks to the Player Card. Player Cards must be submitted for approval before they can be used. More information: %s', 'rank-math' ), '<a href="https://dev.twitter.com/cards/types/player" target="blank">https://dev.twitter.com/cards/types/player</a>' ),
			'dep'     => $player,
		]
	);

	$app = [ [ 'rank_math_twitter_card_type', 'app' ] ];
	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_info',
			'type'    => 'notice',
			'what'    => 'info',
			/* translators: Link to twitter app card doc */
			'content' => sprintf( esc_html__( 'The App Card is a great way to represent mobile applications on Twitter and to drive installs. More information: %s', 'rank-math' ), '<a href="https://dev.twitter.com/cards/types/app" target="blank">https://dev.twitter.com/cards/types/app</a>' ),
			'dep'     => $app,
		]
	);
}

$basic   = [ 'relation' => 'and' ] + $dep;
$basic[] = [ 'rank_math_twitter_card_type', 'app', '!=' ];
$cmb->add_field(
	[
		'id'      => 'rank_math_twitter_image',
		'type'    => 'file',
		'name'    => esc_html__( 'Image', 'rank-math' ),
		'options' => [ 'url' => false ],
		'text'    => [ 'add_upload_file_text' => esc_html__( 'Add Image', 'rank-math' ) ],
		'dep'     => $basic,
		'desc'    => esc_html__( 'Images for this Card support an aspect ratio of 2:1 with minimum dimensions of 300x157 or maximum of 4096x4096 pixels. Images must be less than 5MB in size.', 'rank-math' ),
		'after'   => '<div class="notice notice-warning inline hidden rank-math-notice"><p>' . esc_html__( 'Image is smaller than the minimum size, please select a different image.', 'rank-math' ) . '</p></div>',
	]
);

$cmb->add_field(
	[
		'id'   => 'rank_math_twitter_title',
		'type' => 'text',
		'name' => esc_html__( 'Title', 'rank-math' ),
		'dep'  => $basic,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_twitter_description',
		'type'       => 'textarea',
		'name'       => esc_html__( 'Description', 'rank-math' ),
		'attributes' => [
			'rows'            => 3,
			'data-autoresize' => true,
		],
		'dep'        => $basic,
	]
);

// Image overlay fields.
$img_overlay   = [ 'relation' => 'and' ] + $dep;
$img_overlay[] = [ 'rank_math_twitter_card_type', 'player,app', '!=' ];
$cmb->add_field(
	[
		'id'      => 'rank_math_twitter_enable_image_overlay',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Add icon overlay to thumbnail', 'rank-math' ),
		'desc'    => wp_kses_post( __( '<div class="notice notice-alt notice-warning warning inline rank-math-notice"><p>Please be careful with this option. Although this option will help increace CTR on Facebook, it might get you penalised if over-used.</p></div>', 'rank-math' ) ),
		'default' => $this->do_filter( 'metabox/social/overlay_icon', 'off', 'twitter' ),
		'dep'     => $img_overlay,
	]
);

$img_overlay[] = [ 'rank_math_twitter_enable_image_overlay', 'on' ];
$cmb->add_field(
	[
		'id'      => 'rank_math_twitter_image_overlay',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Icon overlay', 'rank-math' ),
		'options' => Helper::choices_overlay_images( 'names' ),
		'default' => 'play',
		'classes' => 'nob nopb',
		'dep'     => $img_overlay,
	]
);

if ( Admin_Helper::is_user_edit() ) {
	$cmb->add_field(
		[
			'id'   => 'rank_math_twitter_author',
			'type' => 'text',
			'name' => esc_html__( 'Author Twitter Username', 'rank-math' ),
			/* translators: option page link */
			'desc' => sprintf( wp_kses_post( __( 'Insert Twitter username to add twitter:creator tag to posts when the page is shared on Twitter.<br>You can set up default username for fallback in <a href="%s" target="_blank">SEO &raquo; Titles &amp; Meta &raquo; Social</a>.', 'rank-math' ) ), Helper::get_admin_url( 'options-titles#setting-panel-social' ) ),
		]
	);
}

// Player fields.
if ( ! Admin_Helper::is_term_profile_page() ) {
	$cmb->add_field(
		[
			'id'   => 'rank_math_twitter_player_url',
			'type' => 'text',
			'name' => esc_html__( 'Player URL', 'rank-math' ),
			'desc' => esc_html__( 'HTTPS URL to iFrame player. This must be a HTTPS URL which does not generate active mixed content warnings in a web browser. The audio or video player must not require plugins such as Adobe Flash.', 'rank-math' ),
			'dep'  => $player,
		]
	);

	$cmb->add_field(
		[
			'id'   => 'rank_math_twitter_player_size',
			'type' => 'text',
			'name' => esc_html__( 'Player Size', 'rank-math' ),
			'desc' => esc_html__( 'iFrame width and height, specified in pixels in the following format: 600x400.', 'rank-math' ),
			'dep'  => $player,
		]
	);

	$cmb->add_field(
		[
			'id'   => 'rank_math_twitter_player_stream',
			'type' => 'text',
			'name' => esc_html__( 'Stream URL', 'rank-math' ),
			'desc' => esc_html__( 'Optional URL to raw stream that will be rendered in Twitter’s mobile applications directly. If provided, the stream must be delivered in the MPEG-4 container format (the .mp4 extension). The container can store a mix of audio and video with the following codecs: Video: H.264, Baseline Profile (BP), Level 3.0, up to 640 x 480 at 30 fps. Audio: AAC, Low Complexity Profile (LC).', 'rank-math' ),
			'dep'  => $player,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_player_stream_ctype',
			'type'    => 'text',
			'name'    => esc_html__( 'Stream Content Type', 'rank-math' ),
			'desc'    => esc_html__( 'The MIME type/subtype combination that describes the content contained in twitter:player:stream. Takes the form specified in RFC 6381. Currently supported content_type values are those defined in RFC 4337 (MIME Type Registration for MP4).', 'rank-math' ),
			'classes' => 'nob nopb',
			'dep'     => $player,
		]
	);

	// App fields.
	$cmb->add_field(
		[
			'id'   => 'rank_math_twitter_app_description',
			'type' => 'textarea',
			'name' => esc_html__( 'App Description', 'rank-math' ),
			'desc' => esc_html__( 'You can use this as a more concise description than what you may have on the app store. This field has a maximum of 200 characters. (optional)', 'rank-math' ),
			'dep'  => $app,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_iphone_name',
			'type'    => 'text',
			'name'    => esc_html__( 'iPhone App Name', 'rank-math' ),
			'desc'    => esc_html__( 'The name of your app to show.', 'rank-math' ),
			'classes' => 'cmb-row-33',
			'dep'     => $app,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_iphone_id',
			'type'    => 'text',
			'name'    => esc_html__( 'iPhone App ID', 'rank-math' ),
			'desc'    => esc_html__( 'The numeric representation of your app ID in the App Store.', 'rank-math' ),
			'classes' => 'cmb-row-33',
			'dep'     => $app,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_iphone_url',
			'type'    => 'text',
			'name'    => esc_html__( 'iPhone App URL', 'rank-math' ),
			'desc'    => esc_html__( 'Your app\'s custom URL scheme (must include "://").', 'rank-math' ),
			'classes' => 'cmb-row-33',
			'dep'     => $app,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_ipad_name',
			'type'    => 'text',
			'name'    => esc_html__( 'iPad App Name', 'rank-math' ),
			'desc'    => esc_html__( 'The name of your app to show.', 'rank-math' ),
			'classes' => 'cmb-row-33',
			'dep'     => $app,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_ipad_id',
			'type'    => 'text',
			'name'    => esc_html__( 'iPad App ID', 'rank-math' ),
			'desc'    => esc_html__( 'The numeric representation of your app ID in the App Store.', 'rank-math' ),
			'classes' => 'cmb-row-33',
			'dep'     => $app,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_ipad_url',
			'type'    => 'text',
			'name'    => esc_html__( 'iPad App URL', 'rank-math' ),
			'desc'    => esc_html__( 'Your app\'s custom URL scheme (must include "://").', 'rank-math' ),
			'classes' => 'cmb-row-33',
			'dep'     => $app,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_googleplay_name',
			'type'    => 'text',
			'name'    => esc_html__( 'Google Play App Name', 'rank-math' ),
			'desc'    => esc_html__( 'The name of your app to show.', 'rank-math' ),
			'classes' => 'cmb-row-33',
			'dep'     => $app,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_googleplay_id',
			'type'    => 'text',
			'name'    => esc_html__( 'Google Play App ID', 'rank-math' ),
			'desc'    => esc_html__( 'Your app ID in the Google Play (.i.e. "com.android.app")', 'rank-math' ),
			'classes' => 'cmb-row-33',
			'dep'     => $app,
		]
	);

	$cmb->add_field(
		[
			'id'      => 'rank_math_twitter_app_googleplay_url',
			'type'    => 'text',
			'name'    => esc_html__( 'Google Play App URL', 'rank-math' ),
			'desc'    => esc_html__( 'Your app\'s custom URL scheme (must include "://").', 'rank-math' ),
			'classes' => 'cmb-row-33',
			'dep'     => $app,
		]
	);

	$cmb->add_field(
		[
			'id'   => 'rank_math_twitter_app_country',
			'type' => 'text',
			'name' => esc_html__( 'App Country', 'rank-math' ),
			'desc' => esc_html__( 'If your application is not available in the US App Store, you must set this value to the two-letter country code for the App Store that contains your application.', 'rank-math' ),
			'dep'  => $app,
		]
	);
}

$cmb->add_field(
	[
		'id'   => 'setting-panel-social-twitter-close',
		'type' => 'tab',
	]
);

$cmb->add_field(
	[
		'name'    => esc_html__( 'Panel', 'rank-math' ),
		'id'      => 'setting-panel-social-tab-content-end',
		'type'    => 'raw',
		'content' => '</div> <!-- ./rank-math-tabs-content -->
		</div> <!-- ./rank-math-tabs -->',
	]
);
