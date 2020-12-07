<?php
/**
 * The post type settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$post_type = $tab['post_type'];
if ( 'attachment' === $post_type && Helper::get_settings( 'general.attachment_redirect_urls', true ) ) {
	$cmb->add_field(
		[
			'id'      => 'redirect_attachment_notice',
			'type'    => 'notice',
			'what'    => 'warning',
			'content' => esc_html__( 'To configure attachment-related meta tags disable attachment redirection to parent.', 'rank-math' ),
		]
	);
	return;
}

$post_type_obj = get_post_type_object( $post_type );
$name          = $post_type_obj->labels->singular_name;

$custom_default  = 'off';
$richsnp_default = [
	'post'    => 'article',
	'product' => 'product',
];

if ( 'post' === $post_type || 'page' === $post_type ) {
	$custom_default = 'off';
} elseif ( 'attachment' === $post_type ) {
	$custom_default = 'on';
}

$primary_taxonomy_hash = [
	'post'    => 'category',
	'product' => 'product_cat',
];

$is_stories_post_type = defined( 'WEBSTORIES_VERSION' ) && 'web-story' === $post_type;

$cmb->add_field(
	[
		'id'              => 'pt_' . $post_type . '_title',
		'type'            => 'text',
		/* translators: post type name */
		'name'            => sprintf( esc_html__( 'Single %s Title', 'rank-math' ), $name ),
		/* translators: post type name */
		'desc'            => sprintf( esc_html__( 'Default title tag for single %s pages. This can be changed on a per-post basis on the post editor screen.', 'rank-math' ), $name ),
		'classes'         => 'rank-math-supports-variables rank-math-title',
		'default'         => '%title% %page% %sep% %sitename%',
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'pt_' . $post_type . '_description',
		'type'       => 'textarea_small',
		/* translators: post type name */
		'name'       => sprintf( esc_html__( 'Single %s Description', 'rank-math' ), $name ),
		/* translators: post type name */
		'desc'       => sprintf( esc_html__( 'Default description for single %s pages. This can be changed on a per-post basis on the post editor screen.', 'rank-math' ), $name ),
		'classes'    => 'rank-math-supports-variables rank-math-description',
		'default'    => '%excerpt%',
		'attributes' => [
			'class'                  => 'cmb2-textarea-small wp-exclude-emoji',
			'data-gramm_editor'      => 'false',
			'rows'                   => 2,
			'data-exclude-variables' => 'seo_title,seo_description',
		],
	]
);

$cmb->add_field(
	[
		'id'              => 'pt_' . $post_type . '_archive_title',
		'type'            => 'text',
		/* translators: post type name */
		'name'            => sprintf( esc_html__( '%s Archive Title', 'rank-math' ), $name ),
		/* translators: post type name */
		'desc'            => sprintf( esc_html__( 'Title for %s archive pages.', 'rank-math' ), $name ),
		'classes'         => 'rank-math-supports-variables rank-math-title',
		'default'         => '%title% %page% %sep% %sitename%',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'pt_' . $post_type . '_archive_description',
		'type'       => 'textarea_small',
		/* translators: post type name */
		'name'       => sprintf( esc_html__( '%s Archive Description', 'rank-math' ), $name ),
		/* translators: post type name */
		'desc'       => sprintf( esc_html__( 'Description for %s archive pages.', 'rank-math' ), $name ),
		'classes'    => 'rank-math-supports-variables rank-math-description',
		'attributes' => [
			'data-exclude-variables' => 'seo_title,seo_description',
			'rows'                   => 2,
		],
	]
);

if ( ( class_exists( 'WooCommerce' ) && 'product' === $post_type ) || ( class_exists( 'Easy_Digital_Downloads' ) && 'download' === $post_type ) ) {

	$cmb->add_field(
		[
			'id'      => 'pt_' . $post_type . '_default_rich_snippet',
			'type'    => 'radio_inline',
			'name'    => esc_html__( 'Schema Type', 'rank-math' ),
			/* translators: link to title setting screen */
			'desc'    => __( 'Default rich snippet selected when creating a new product.', 'rank-math' ),
			'options' => [
				'off'     => esc_html__( 'None', 'rank-math' ),
				'product' => 'download' === $post_type ? esc_html__( 'EDD Product', 'rank-math' ) : esc_html__( 'WooCommerce Product', 'rank-math' ),
			],
			'default' => $this->do_filter( 'settings/snippet/type', 'product', $post_type ),
		]
	);

} else {

	$cmb->add_field(
		[
			'id'         => 'pt_' . $post_type . '_default_rich_snippet',
			'type'       => 'select',
			'name'       => esc_html__( 'Schema Type', 'rank-math' ),
			'desc'       => esc_html__( 'Default rich snippet selected when creating a new post of this type. ', 'rank-math' ),
			'options'    => $is_stories_post_type ? [
				'off'     => esc_html__( 'None', 'rank-math' ),
				'article' => esc_html__( 'Article', 'rank-math' ),
			] : Helper::choices_rich_snippet_types( esc_html__( 'None (Click here to set one)', 'rank-math' ) ),
			'default'    => $this->do_filter( 'settings/snippet/type', isset( $richsnp_default[ $post_type ] ) ? $richsnp_default[ $post_type ] : 'off', $post_type ),
			'attributes' => ! $is_stories_post_type ? [ 'data-s2' => '' ] : '',
		]
	);

	// Common fields.
	$cmb->add_field(
		[
			'id'              => 'pt_' . $post_type . '_default_snippet_name',
			'type'            => 'text',
			'name'            => esc_html__( 'Headline', 'rank-math' ),
			'dep'             => [ [ 'pt_' . $post_type . '_default_rich_snippet', 'off', '!=' ] ],
			'classes'         => 'rank-math-supports-variables rank-math-advanced-option',
			'default'         => '%seo_title%',
			'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		]
	);

	$cmb->add_field(
		[
			'id'         => 'pt_' . $post_type . '_default_snippet_desc',
			'type'       => 'textarea',
			'name'       => esc_html__( 'Description', 'rank-math' ),
			'attributes' => [
				'class'           => 'cmb2_textarea wp-exclude-emoji',
				'rows'            => 3,
				'data-autoresize' => true,
			],
			'classes'    => 'rank-math-supports-variables rank-math-advanced-option',
			'default'    => '%seo_description%',
			'dep'        => [ [ 'pt_' . $post_type . '_default_rich_snippet', 'off,book,local', '!=' ] ],
		]
	);
}

// Article fields.
$article_dep = [ [ 'pt_' . $post_type . '_default_rich_snippet', 'article' ] ];
/* translators: Google article snippet doc link */
$article_desc = 'person' === Helper::get_settings( 'titles.knowledgegraph_type' ) ? '<div class="notice notice-warning inline rank-math-notice"><p>' . sprintf( __( 'Google does not allow Person as the Publisher for articles. Organization will be used instead. You can read more about this <a href="%s" target="_blank">here</a>.', 'rank-math' ), \RankMath\KB::get( 'article' ) ) . '</p></div>' : '';
$cmb->add_field(
	[
		'id'      => 'pt_' . $post_type . '_default_article_type',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Article Type', 'rank-math' ),
		'options' => [
			'Article'     => esc_html__( 'Article', 'rank-math' ),
			'BlogPosting' => esc_html__( 'Blog Post', 'rank-math' ),
			'NewsArticle' => esc_html__( 'News Article', 'rank-math' ),
		],
		'default' => $this->do_filter( 'settings/snippet/article_type', 'post' === $post_type ? 'BlogPosting' : 'Article', $post_type ),
		'desc'    => $article_desc,
		'dep'     => $article_dep,
	]
);

$cmb->add_field(
	[
		'id'      => 'pt_' . $post_type . '_custom_robots',
		'type'    => 'toggle',
		/* translators: post type name */
		'name'    => sprintf( esc_html__( '%s Robots Meta', 'rank-math' ), $name ),
		/* translators: post type name */
		'desc'    => sprintf( wp_kses_post( __( 'Select custom robots meta, such as <code>nofollow</code>, <code>noarchive</code>, etc. for single %s pages. Otherwise the default meta will be used, as set in the Global Meta tab.', 'rank-math' ) ), $name ),
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
		'id'                => 'pt_' . $post_type . '_robots',
		'type'              => 'multicheck',
		/* translators: post type name */
		'name'              => sprintf( esc_html__( '%s Robots Meta', 'rank-math' ), $name ),
		/* translators: post type name */
		'desc'              => sprintf( esc_html__( 'Custom values for robots meta tag on %s.', 'rank-math' ), $name ),
		'options'           => Helper::choices_robots(),
		'select_all_button' => false,
		'dep'               => [ [ 'pt_' . $post_type . '_custom_robots', 'on' ] ],
		'classes'           => 'rank-math-advanced-option rank-math-robots-data',
		'default'           => [ 'index' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'pt_' . $post_type . '_advanced_robots',
		'type'            => 'advanced_robots',
		/* translators: post type name */
		'name'            => sprintf( esc_html__( '%s Advanced Robots Meta', 'rank-math' ), $name ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_advanced_robots' ],
		'dep'             => [ [ 'pt_' . $post_type . '_custom_robots', 'on' ] ],
		'classes'         => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'pt_' . $post_type . '_link_suggestions',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Link Suggestions', 'rank-math' ),
		'desc'    => esc_html__( 'Enable Link Suggestions meta box for this post type, along with the Pillar Content feature.', 'rank-math' ),
		'default' => $this->do_filter( 'settings/titles/link_suggestions', 'on', $post_type ),
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'pt_' . $post_type . '_ls_use_fk',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Link Suggestion Titles', 'rank-math' ),
		'desc'    => esc_html__( 'Use the Focus Keyword as the default text for the links instead of the post titles.', 'rank-math' ),
		'options' => [
			'titles'         => esc_html__( 'Titles', 'rank-math' ),
			'focus_keywords' => esc_html__( 'Focus Keywords', 'rank-math' ),
		],
		'default' => 'titles',
		'dep'     => [ [ 'pt_' . $post_type . '_link_suggestions', 'on' ] ],
		'classes' => 'rank-math-advanced-option',
	]
);

$taxonomies = Helper::get_object_taxonomies( $post_type );
if ( $taxonomies ) {
	$cmb->add_field(
		[
			'id'      => 'pt_' . $post_type . '_primary_taxonomy',
			'type'    => 'select',
			'name'    => esc_html__( 'Primary Taxonomy', 'rank-math' ),
			/* translators: post type name */
			'desc'    => sprintf( esc_html__( 'Select taxonomy to show in the Breadcrumbs when a single %1$s is being viewed.', 'rank-math' ), $name ),
			'options' => $taxonomies,
			'default' => isset( $primary_taxonomy_hash[ $post_type ] ) ? $primary_taxonomy_hash[ $post_type ] : 'off',
			'classes' => 'rank-math-advanced-option',
		]
	);
}

$cmb->add_field(
	[
		'id'   => 'pt_' . $post_type . '_facebook_image',
		'type' => 'file',
		'name' => esc_html__( 'Thumbnail for Facebook', 'rank-math' ),
		'desc' => esc_html__( 'Image displayed when your page is shared on Facebook and other social networks. Use images that are at least 1200 x 630 pixels for the best display on high resolution devices.', 'rank-math' ),
	]
);

// Enable/Disable Metabox option.
if ( 'attachment' === $post_type ) {
	$cmb->add_field(
		[
			'id'      => 'pt_' . $post_type . '_bulk_editing',
			'type'    => 'radio_inline',
			'name'    => esc_html__( 'Bulk Editing', 'rank-math' ),
			'desc'    => esc_html__( 'Add bulk editing columns to the post listing screen.', 'rank-math' ),
			'options' => [
				'0'        => esc_html__( 'Disabled', 'rank-math' ),
				'editing'  => esc_html__( 'Enabled', 'rank-math' ),
				'readonly' => esc_html__( 'Read Only', 'rank-math' ),
			],
			'default' => 'editing',
			'classes' => 'rank-math-advanced-option',
		]
	);
} else {
	$cmb->add_field(
		[
			'id'      => 'pt_' . $post_type . '_add_meta_box',
			'type'    => 'toggle',
			'name'    => esc_html__( 'Add SEO Meta Box', 'rank-math' ),
			'desc'    => esc_html__( 'Add the SEO Meta Box for the editor screen to customize SEO options for posts in this post type.', 'rank-math' ),
			'default' => 'on',
			'classes' => 'rank-math-advanced-option',
		]
	);

	$cmb->add_field(
		[
			'id'      => 'pt_' . $post_type . '_bulk_editing',
			'type'    => 'radio_inline',
			'name'    => esc_html__( 'Bulk Editing', 'rank-math' ),
			'desc'    => esc_html__( 'Add bulk editing columns to the post listing screen.', 'rank-math' ),
			'options' => [
				'0'        => esc_html__( 'Disabled', 'rank-math' ),
				'editing'  => esc_html__( 'Enabled', 'rank-math' ),
				'readonly' => esc_html__( 'Read Only', 'rank-math' ),
			],
			'default' => 'editing',
			'dep'     => [ [ 'pt_' . $post_type . '_add_meta_box', 'on' ] ],
			'classes' => 'rank-math-advanced-option',
		]
	);

	$cmb->add_field(
		[
			'id'      => 'pt_' . $post_type . '_analyze_fields',
			'type'    => 'textarea_small',
			'name'    => esc_html__( 'Custom Fields', 'rank-math' ),
			'desc'    => esc_html__( 'List of custom fields name to include in the Page analysis. Add one per line.', 'rank-math' ),
			'default' => '',
			'classes' => 'rank-math-advanced-option',
		]
	);
}

// Archive not enabled.
if ( ! $post_type_obj->has_archive ) {
	$cmb->remove_field( 'pt_' . $post_type . '_archive_title' );
	$cmb->remove_field( 'pt_' . $post_type . '_archive_description' );
	$cmb->remove_field( 'pt_' . $post_type . '_facebook_image' );
}

if ( 'attachment' === $post_type ) {
	$cmb->remove_field( 'pt_' . $post_type . '_link_suggestions' );
	$cmb->remove_field( 'pt_' . $post_type . '_ls_use_fk' );
}

if ( $is_stories_post_type ) {
	$cmb->remove_field( 'pt_' . $post_type . '_default_snippet_desc' );
	$cmb->remove_field( 'pt_' . $post_type . '_description' );
	$cmb->remove_field( 'pt_' . $post_type . '_link_suggestions' );
	$cmb->remove_field( 'pt_' . $post_type . '_ls_use_fk' );
	$cmb->remove_field( 'pt_' . $post_type . '_analyze_fields' );
	$cmb->remove_field( 'pt_' . $post_type . '_bulk_editing' );
	$cmb->remove_field( 'pt_' . $post_type . '_add_meta_box' );
}
