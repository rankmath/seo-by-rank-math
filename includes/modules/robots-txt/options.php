<?php
/**
 * The robots.txt settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Robots_Txt;
use RankMath\Helper;

$data       = Robots_Txt::get_robots_data();
$attributes = [];
if ( $data['exists'] ) {
	$attributes['readonly'] = 'readonly';
	$attributes['value']    = $data['default'];
} else {
	$attributes['placeholder'] = $data['default'];
}

if ( 0 === $data['public'] ) {
	$attributes['disabled'] = 'disabled';
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
		'desc'            => ! $data['exists'] ? '' : esc_html__( 'Contents are locked because robots.txt file is present in the root folder.', 'rank-math' ),
		'attributes'      => $attributes,
		'classes'         => 'nob rank-math-code-box',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_robots_text' ],
	]
);

if ( 0 === $data['public'] ) {
	$cmb->add_field(
		[
			'id'      => 'site_not_public',
			'type'    => 'notice',
			'what'    => 'warning',
			'classes' => 'nob nopt rank-math-notice',
			'content' => wp_kses_post(
				sprintf(
					__( '<strong>Warning:</strong> your site\'s search engine visibility is set to Hidden in <a href="%1$s" target="_blank">Settings > Reading</a>. This means that the changes you make here will not take effect. Set the search engine visibility to Public to be able to change the robots.txt content.', 'rank-math' ),
					admin_url( 'options-reading.php' )
				)
			),
		]
	);
	return;
}
