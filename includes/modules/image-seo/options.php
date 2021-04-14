<?php
/**
 * The Image SEO settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Image_Seo
 */

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'add_img_alt',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Add missing ALT attributes', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'Add <code>alt</code> attributes for <code>images</code> without <code>alt</code> attributes automatically. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.', 'rank-math' ) ),
		'default' => 'off',
	]
);

$cmb->add_field(
	[
		'id'              => 'img_alt_format',
		'type'            => 'text',
		'name'            => esc_html__( 'Alt attribute format', 'rank-math' ),
		'desc'            => wp_kses_post( __( 'Format used for the new <code>alt</code> attribute values.', 'rank-math' ) ),
		'classes'         => 'large-text rank-math-supports-variables',
		'default'         => ' %filename%',
		'dep'             => [ [ 'add_img_alt', 'on' ] ],
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'      => 'add_img_title',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Add missing TITLE attributes', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'Add <code>TITLE</code> attribute for all <code>images</code> without a <code>TITLE</code> attribute automatically. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.', 'rank-math' ) ),
		'default' => 'off',
	]
);

$cmb->add_field(
	[
		'id'              => 'img_title_format',
		'type'            => 'text',
		'name'            => esc_html__( 'Title attribute format', 'rank-math' ),
		'desc'            => wp_kses_post( __( 'Format used for the new <code>title</code> attribute values.', 'rank-math' ) ),
		'classes'         => 'large-text rank-math-supports-variables dropdown-up',
		'default'         => '%title% %count(title)%',
		'dep'             => [ [ 'add_img_title', 'on' ] ],
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);
