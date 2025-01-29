<?php
/**
 * The robots.txt settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Robots_Txt;
use RankMath\KB;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$data       = Robots_Txt::get_robots_data();
$attributes = [ 'data-gramm' => 'false' ];
$desc       = '';
if ( $data['exists'] ) {
	$attributes['readonly'] = 'readonly';
	$attributes['value']    = $data['default'];
	$desc                   = esc_html__( 'Contents are locked because a robots.txt file is present in the root folder.', 'rank-math' );
} else {
	$attributes['placeholder'] = $data['default'];
}

if ( isset( $data['writable'] ) && false === $data['writable'] ) {
	$attributes['placeholder'] = $data['default'];
	$desc                      = esc_html__( 'Rank Math could not detect if a robots.txt file exists or not because of a filesystem issue. The file contents entered here may not be applied.', 'rank-math' );

	unset( $attributes['readonly'], $attributes['value'] );
}

if ( 0 === $data['public'] ) {
	$attributes['readonly'] = 'readonly';
}

if ( ! Helper::is_edit_allowed() ) {
	$cmb->add_field(
		[
			'id'      => 'edit_disabled',
			'type'    => 'notice',
			'what'    => 'error',
			'content' => __( 'robots.txt file is not writable.', 'rank-math' ),
		]
	);
	$attributes['disabled'] = 'disabled';
}

$cmb->add_field(
	[
		'id'              => 'robots_txt_content',
		'type'            => 'textarea',
		'attributes'      => $attributes,
		'classes'         => 'nob rank-math-code-box',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_robots_text' ],
	]
);

if ( $desc ) {
	$cmb->add_field(
		[
			'id'      => 'robots_locked',
			'type'    => 'notice',
			'what'    => 'warning',
			'classes' => 'nob nopt rank-math-notice',
			'content' => wp_kses_post( $desc ),
		]
	);
} elseif ( 0 === $data['public'] ) {
	$cmb->add_field(
		[
			'id'      => 'site_not_public',
			'type'    => 'notice',
			'what'    => 'warning',
			'classes' => 'nob nopt rank-math-notice',
			'content' => wp_kses_post(
				sprintf(
					// Translators: placeholder is the Settings page URL.
					__( '<strong>Warning:</strong> your site\'s search engine visibility is set to Hidden in <a href="%1$s" target="_blank">Settings &gt; Reading</a>. This means that the changes you make here will not take effect. Set the search engine visibility to Public to be able to change the robots.txt content.', 'rank-math' ),
					admin_url( 'options-reading.php' )
				)
			),
		]
	);
}

$cmb->add_field(
	[
		'id'      => 'robots_tester',
		'type'    => 'notice',
		'what'    => 'info',
		'classes' => 'nob nopt rank-math-notice',
		'content' => wp_kses_post(
			sprintf(
				// Translators: placeholder is the URL to the robots.txt tester tool.
				__( 'Test and edit your live robots.txt file with our <a href="%1$s" target="_blank">Robots.txt Tester</a>.', 'rank-math' ),
				KB::get( 'robotstxt-tool', 'Options Panel Robots.txt Tester' ) . '&url=' . rawurlencode( home_url( '/' ) )
			)
		),
	]
);
