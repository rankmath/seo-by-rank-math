<?php
/**
 * The taxonomies settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$taxonomy     = $tab['taxonomy'];
$taxonomy_obj = get_taxonomy( $taxonomy );
$name         = $taxonomy_obj->labels->singular_name;

$metabox_default = 'off';
$custom_default  = 'off';

if ( 'category' === $taxonomy ) {
	$metabox_default = 'on';
	$custom_default  = 'off';
} elseif ( 'post_tag' === $taxonomy ) {
	$metabox_default = 'off';
	$custom_default  = 'on';
} elseif ( 'post_format' === $taxonomy ) {
	$custom_default = 'on';
}

$cmb->add_field(
	[
		'id'              => 'tax_' . $taxonomy . '_title',
		'type'            => 'text',
		/* translators: taxonomy name */
		'name'            => sprintf( esc_html__( '%s Archive Titles', 'rank-math' ), $name ),
		/* translators: taxonomy name */
		'desc'            => sprintf( esc_html__( 'Title tag for %s archives', 'rank-math' ), $name ),
		'classes'         => 'rank-math-supports-variables rank-math-title',
		'default'         => '%term% Archives %page% %sep% %sitename%',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'tax_' . $taxonomy . '_description',
		'type'       => 'textarea_small',
		/* translators: taxonomy name */
		'name'       => sprintf( esc_html__( '%s Archive Descriptions', 'rank-math' ), $name ),
		/* translators: taxonomy name */
		'desc'       => sprintf( esc_html__( 'Description for %s archives', 'rank-math' ), $name ),
		'classes'    => 'rank-math-supports-variables rank-math-description',
		'attributes' => [
			'class'                  => 'cmb2-textarea-small wp-exclude-emoji',
			'data-gramm'             => 'false',
			'rows'                   => 2,
			'data-exclude-variables' => 'seo_title,seo_description',
		],
		'default'    => '%term_description%',
	]
);

$cmb->add_field(
	[
		'id'      => 'tax_' . $taxonomy . '_custom_robots',
		'type'    => 'toggle',
		/* translators: taxonomy name */
		'name'    => sprintf( esc_html__( '%s Archives Robots Meta', 'rank-math' ), $name ),
		/* translators: taxonomy name */
		'desc'    => sprintf( wp_kses_post( __( 'Select custom robots meta, such as <code>nofollow</code>, <code>noarchive</code>, etc. for %s archive pages. Otherwise the default meta will be used, as set in the Global Meta tab.', 'rank-math' ) ), strtolower( $name ) ),
		'options' => [
			'off' => esc_html__( 'Default', 'rank-math' ),
			'on'  => esc_html__( 'Custom', 'rank-math' ),
		],
		'default' => $custom_default,
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'                => 'tax_' . $taxonomy . '_robots',
		'type'              => 'multicheck',
		/* translators: taxonomy name */
		'name'              => sprintf( esc_html__( '%s Archives Robots Meta', 'rank-math' ), $name ),
		/* translators: taxonomy name */
		'desc'              => sprintf( esc_html__( 'Custom values for robots meta tag on %s archives.', 'rank-math' ), $name ),
		'options'           => Helper::choices_robots(),
		'select_all_button' => false,
		'dep'               => [ [ 'tax_' . $taxonomy . '_custom_robots', 'on' ] ],
		'classes'           => 'rank-math-advanced-option rank-math-robots-data',
		'default'           => [ 'index' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'tax_' . $taxonomy . '_advanced_robots',
		'type'            => 'advanced_robots',
		/* translators: taxonomy name */
		'name'            => sprintf( esc_html__( '%s Archives Advanced Robots Meta', 'rank-math' ), $name ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_advanced_robots' ],
		'dep'             => [ [ 'tax_' . $taxonomy . '_custom_robots', 'on' ] ],
		'classes'         => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'tax_' . $taxonomy . '_slack_enhanced_sharing',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Slack Enhanced Sharing', 'rank-math' ),
		'desc'    => esc_html__( 'When the option is enabled and a term from this taxonomy is shared on Slack, additional information will be shown (the total number of items with this term).', 'rank-math' ),
		'default' => 'on',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'tax_' . $taxonomy . '_add_meta_box',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Add SEO Controls', 'rank-math' ),
		'desc'    => esc_html__( 'Add the SEO Controls for the term editor screen to customize SEO options for individual terms in this taxonomy.', 'rank-math' ),
		'default' => $metabox_default,
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'remove_' . $taxonomy . '_snippet_data',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Remove Snippet Data', 'rank-math' ),
		/* translators: taxonomy name */
		'desc'    => sprintf( esc_html__( 'Remove schema data from %s.', 'rank-math' ), $name ),
		'default' => ( in_array( $taxonomy, [ 'product_cat', 'product_tag' ], true ) ) ? 'on' : 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

if ( 'post_format' === $taxonomy ) {
	$cmb->remove_field( 'tax_' . $taxonomy . '_add_meta_box' );
	$cmb->remove_field( 'remove_' . $taxonomy . '_snippet_data' );
}
