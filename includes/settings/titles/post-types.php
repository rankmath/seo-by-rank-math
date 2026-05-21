<?php
/**
 * The post type settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;
use RankMath\KB;

defined( 'ABSPATH' ) || exit;

$current_post_type = $tab['post_type'];
if ( 'attachment' === $current_post_type && Helper::get_settings( 'general.attachment_redirect_urls', true ) ) {
	$cmb->add_field(
		[
			'id'      => 'redirect_attachment_notice',
			'type'    => 'notice',
			'what'    => 'warning',
			/* translators: The settings page link */
			'content' => sprintf( __( 'To configure meta tags for your media attachment pages, you need to first %s to parent.', 'seo-by-rank-math' ), '<a href="' . esc_url( Helper::get_admin_url( 'options-general#setting-panel-links' ) ) . '">' . esc_html__( 'disable redirect attachments', 'seo-by-rank-math' ) . '</a>' ),
		]
	);
		return;
}

$post_type_obj = get_post_type_object( $current_post_type );
$name          = $post_type_obj->labels->singular_name;

$custom_default  = 'off';
$richsnp_default = [
	'post'    => 'article',
	'product' => 'product',
];

if ( 'post' === $current_post_type || 'page' === $current_post_type ) {
	$custom_default = 'off';
} elseif ( 'attachment' === $current_post_type ) {
	$custom_default = 'on';
}

$primary_taxonomy_hash = [
	'post'    => 'category',
	'product' => 'product_cat',
];

$is_stories_post_type = defined( 'WEBSTORIES_VERSION' ) && 'web-story' === $current_post_type;

// Translators: Post type name.
$slack_enhanced_sharing_description = sprintf( __( 'When the option is enabled and a %s is shared on Slack, additional information will be shown (estimated time to read and author).', 'seo-by-rank-math' ), $name );
if ( 'page' === $current_post_type ) {
	$slack_enhanced_sharing_description = __( 'When the option is enabled and a page is shared on Slack, additional information will be shown (estimated time to read).', 'seo-by-rank-math' );
} elseif ( 'product' === $current_post_type ) {
	$slack_enhanced_sharing_description = __( 'When the option is enabled and a product is shared on Slack, additional information will be shown (price & availability).', 'seo-by-rank-math' );
} elseif ( 'download' === $current_post_type ) {
	$slack_enhanced_sharing_description = __( 'When the option is enabled and a product is shared on Slack, additional information will be shown (price).', 'seo-by-rank-math' );
}

$cmb->add_field(
	[
		'id'              => 'pt_' . $current_post_type . '_title',
		'type'            => 'text',
		/* translators: post type name */
		'name'            => sprintf( esc_html__( 'Single %s Title', 'seo-by-rank-math' ), $name ),
		/* translators: post type name */
		'desc'            => sprintf( esc_html__( 'Default title tag for single %s pages. This can be changed on a per-post basis on the post editor screen.', 'seo-by-rank-math' ), $name ),
		'classes'         => 'rank-math-supports-variables rank-math-title',
		'default'         => '%title% %page% %sep% %sitename%',
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'pt_' . $current_post_type . '_description',
		'type'       => 'textarea_small',
		/* translators: post type name */
		'name'       => sprintf( esc_html__( 'Single %s Description', 'seo-by-rank-math' ), $name ),
		/* translators: post type name */
		'desc'       => sprintf( esc_html__( 'Default description for single %s pages. This can be changed on a per-post basis on the post editor screen.', 'seo-by-rank-math' ), $name ),
		'classes'    => 'rank-math-supports-variables rank-math-description',
		'default'    => '%excerpt%',
		'attributes' => [
			'class'                  => 'cmb2-textarea-small wp-exclude-emoji',
			'data-gramm'             => 'false',
			'rows'                   => 2,
			'data-exclude-variables' => 'seo_title,seo_description',
		],
	]
);

$cmb->add_field(
	[
		'id'              => 'pt_' . $current_post_type . '_archive_title',
		'type'            => 'text',
		/* translators: post type name */
		'name'            => sprintf( esc_html__( '%s Archive Title', 'seo-by-rank-math' ), $name ),
		/* translators: post type name */
		'desc'            => sprintf( esc_html__( 'Title for %s archive pages.', 'seo-by-rank-math' ), $name ),
		'classes'         => 'rank-math-supports-variables rank-math-title',
		'default'         => '%title% %page% %sep% %sitename%',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'pt_' . $current_post_type . '_archive_description',
		'type'       => 'textarea_small',
		/* translators: post type name */
		'name'       => sprintf( esc_html__( '%s Archive Description', 'seo-by-rank-math' ), $name ),
		/* translators: post type name */
		'desc'       => sprintf( esc_html__( 'Description for %s archive pages.', 'seo-by-rank-math' ), $name ),
		'classes'    => 'rank-math-supports-variables rank-math-description',
		'attributes' => [
			'data-exclude-variables' => 'seo_title,seo_description',
			'rows'                   => 2,
		],
	]
);

if ( ( class_exists( 'WooCommerce' ) && 'product' === $current_post_type ) || ( class_exists( 'Easy_Digital_Downloads' ) && 'download' === $current_post_type ) ) {

	$cmb->add_field(
		[
			'id'      => 'pt_' . $current_post_type . '_default_rich_snippet',
			'type'    => 'radio_inline',
			'name'    => esc_html__( 'Schema Type', 'seo-by-rank-math' ),
			/* translators: link to title setting screen */
			'desc'    => __( 'Default rich snippet selected when creating a new product.', 'seo-by-rank-math' ),
			'options' => [
				'off'     => esc_html__( 'None', 'seo-by-rank-math' ),
				'product' => 'download' === $current_post_type ? esc_html__( 'EDD Product', 'seo-by-rank-math' ) : esc_html__( 'WooCommerce Product', 'seo-by-rank-math' ),
			],
			'default' => $this->do_filter( 'settings/snippet/type', 'product', $current_post_type ),
		]
	);

} else {

	$schema_types = Helper::choices_rich_snippet_types( esc_html__( 'None (Click here to set one)', 'seo-by-rank-math' ), $current_post_type );
	$field_type   = 'select';
	$attributes   = ! $is_stories_post_type ? [ 'data-s2' => '' ] : '';
	$default      = isset( $richsnp_default[ $current_post_type ] ) ? $richsnp_default[ $current_post_type ] : 'off';

	if ( 2 === count( $schema_types ) ) {
		$field_type = 'radio_inline';
		$attributes = '';
		$default    = array_key_last( $schema_types );
	}

	$cmb->add_field(
		[
			'id'         => 'pt_' . $current_post_type . '_default_rich_snippet',
			'type'       => $field_type,
			'name'       => esc_html__( 'Schema Type', 'seo-by-rank-math' ),
			'desc'       => sprintf(
				// Translators: %s is "Article" inside a <code> tag.
				esc_html__( 'Default rich snippet selected when creating a new post of this type. If %s is selected, it will be applied for all existing posts with no Schema selected.', 'seo-by-rank-math' ),
				'<code>' . esc_html_x( 'Article', 'Schema type name in a field description', 'seo-by-rank-math' ) . '</code>'
			),
			'options'    => $is_stories_post_type ? [
				'off'     => esc_html__( 'None', 'seo-by-rank-math' ),
				'article' => esc_html__( 'Article', 'seo-by-rank-math' ),
			] : $schema_types,
			'default'    => $this->do_filter( 'settings/snippet/type', $default, $current_post_type ),
			'attributes' => $attributes,
		]
	);

	// Common fields.
	$cmb->add_field(
		[
			'id'              => 'pt_' . $current_post_type . '_default_snippet_name',
			'type'            => 'text',
			'name'            => esc_html__( 'Headline', 'seo-by-rank-math' ),
			'dep'             => [ [ 'pt_' . $current_post_type . '_default_rich_snippet', 'off', '!=' ] ],
			'classes'         => 'rank-math-supports-variables rank-math-advanced-option',
			'default'         => '%seo_title%',
			'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		]
	);

	$cmb->add_field(
		[
			'id'         => 'pt_' . $current_post_type . '_default_snippet_desc',
			'type'       => 'textarea',
			'name'       => esc_html__( 'Description', 'seo-by-rank-math' ),
			'attributes' => [
				'class'           => 'cmb2_textarea wp-exclude-emoji',
				'rows'            => 3,
				'data-autoresize' => true,
			],
			'classes'    => 'rank-math-supports-variables rank-math-advanced-option',
			'default'    => '%seo_description%',
			'dep'        => [ [ 'pt_' . $current_post_type . '_default_rich_snippet', 'off,book,local', '!=' ] ],
		]
	);
}

// Article fields.
$article_dep = [ [ 'pt_' . $current_post_type . '_default_rich_snippet', 'article' ] ];
/* translators: Google article snippet doc link */
$article_desc = 'person' === Helper::get_settings( 'titles.knowledgegraph_type' ) ? '<div class="notice notice-warning inline rank-math-notice"><p>' . sprintf( __( 'Google does not allow Person as the Publisher for articles. Organization will be used instead. You can read more about this <a href="%s" target="_blank">here</a>.', 'seo-by-rank-math' ), KB::get( 'google-article-schema' ) ) . '</p></div>' : '';
$cmb->add_field(
	[
		'id'      => 'pt_' . $current_post_type . '_default_article_type',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Article Type', 'seo-by-rank-math' ),
		'options' => [
			'Article'     => esc_html__( 'Article', 'seo-by-rank-math' ),
			'BlogPosting' => esc_html__( 'Blog Post', 'seo-by-rank-math' ),
			'NewsArticle' => esc_html__( 'News Article', 'seo-by-rank-math' ),
		],
		'default' => $this->do_filter( 'settings/snippet/article_type', 'post' === $current_post_type ? 'BlogPosting' : 'Article', $current_post_type ),
		'desc'    => $article_desc,
		'dep'     => $article_dep,
	]
);

$cmb->add_field(
	[
		'id'      => 'pt_' . $current_post_type . '_custom_robots',
		'type'    => 'toggle',
		/* translators: post type name */
		'name'    => sprintf( esc_html__( '%s Robots Meta', 'seo-by-rank-math' ), $name ),
		/* translators: post type name */
		'desc'    => sprintf( wp_kses_post( __( 'Select custom robots meta, such as <code>nofollow</code>, <code>noarchive</code>, etc. for single %s pages. Otherwise the default meta will be used, as set in the Global Meta tab.', 'seo-by-rank-math' ) ), $name ),
		'options' => [
			'off' => esc_html__( 'Default', 'seo-by-rank-math' ),
			'on'  => esc_html__( 'Custom', 'seo-by-rank-math' ),
		],
		'default' => $custom_default,
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'                => 'pt_' . $current_post_type . '_robots',
		'type'              => 'multicheck',
		/* translators: post type name */
		'name'              => sprintf( esc_html__( '%s Robots Meta', 'seo-by-rank-math' ), $name ),
		/* translators: post type name */
		'desc'              => sprintf( esc_html__( 'Custom values for robots meta tag on %s.', 'seo-by-rank-math' ), $name ),
		'options'           => Helper::choices_robots(),
		'select_all_button' => false,
		'dep'               => [ [ 'pt_' . $current_post_type . '_custom_robots', 'on' ] ],
		'classes'           => 'rank-math-advanced-option rank-math-robots-data',
		'default'           => [ 'index' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'pt_' . $current_post_type . '_advanced_robots',
		'type'            => 'advanced_robots',
		/* translators: post type name */
		'name'            => sprintf( esc_html__( '%s Advanced Robots Meta', 'seo-by-rank-math' ), $name ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_advanced_robots' ],
		'dep'             => [ [ 'pt_' . $current_post_type . '_custom_robots', 'on' ] ],
		'classes'         => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'pt_' . $current_post_type . '_link_suggestions',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Link Suggestions', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Enable Link Suggestions meta box for this post type, along with the Pillar Content feature.', 'seo-by-rank-math' ),
		'default' => $this->do_filter( 'settings/titles/link_suggestions', 'on', $current_post_type ),
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'pt_' . $current_post_type . '_ls_use_fk',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Link Suggestion Titles', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Use the Focus Keyword as the default text for the links instead of the post titles.', 'seo-by-rank-math' ),
		'options' => [
			'titles'         => esc_html__( 'Titles', 'seo-by-rank-math' ),
			'focus_keywords' => esc_html__( 'Focus Keywords', 'seo-by-rank-math' ),
		],
		'default' => 'titles',
		'dep'     => [ [ 'pt_' . $current_post_type . '_link_suggestions', 'on' ] ],
		'classes' => 'rank-math-advanced-option',
	]
);

$taxonomies = Helper::get_object_taxonomies( $current_post_type );
if ( $taxonomies ) {
	$cmb->add_field(
		[
			'id'      => 'pt_' . $current_post_type . '_primary_taxonomy',
			'type'    => 'select',
			'name'    => esc_html__( 'Primary Taxonomy', 'seo-by-rank-math' ),
			/* translators: post type name */
			'desc'    => sprintf( esc_html__( 'Choose which taxonomy you want to use with the Primary Term feature. This will also be the taxonomy shown in the Breadcrumbs when a single %1$s is being viewed.', 'seo-by-rank-math' ), $name ),
			'options' => $taxonomies,
			'default' => isset( $primary_taxonomy_hash[ $current_post_type ] ) ? $primary_taxonomy_hash[ $current_post_type ] : 'off',
			'classes' => 'rank-math-advanced-option',
		]
	);
}

$cmb->add_field(
	[
		'id'   => 'pt_' . $current_post_type . '_facebook_image',
		'type' => 'file',
		'name' => esc_html__( 'Thumbnail for Facebook', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'Image displayed when your page is shared on Facebook and other social networks. Use images that are at least 1200 x 630 pixels for the best display on high resolution devices.', 'seo-by-rank-math' ),
	]
);

// Enable/Disable Metabox option.
if ( 'attachment' === $current_post_type ) {
	$cmb->add_field(
		[
			'id'      => 'pt_' . $current_post_type . '_bulk_editing',
			'type'    => 'radio_inline',
			'name'    => esc_html__( 'Bulk Editing', 'seo-by-rank-math' ),
			'desc'    => esc_html__( 'Add bulk editing columns to the post listing screen.', 'seo-by-rank-math' ),
			'options' => [
				'0'        => esc_html__( 'Disabled', 'seo-by-rank-math' ),
				'editing'  => esc_html__( 'Enabled', 'seo-by-rank-math' ),
				'readonly' => esc_html__( 'Read Only', 'seo-by-rank-math' ),
			],
			'default' => 'editing',
			'classes' => 'rank-math-advanced-option',
		]
	);
} else {
	$cmb->add_field(
		[
			'id'      => 'pt_' . $current_post_type . '_slack_enhanced_sharing',
			'type'    => 'toggle',
			'name'    => esc_html__( 'Slack Enhanced Sharing', 'seo-by-rank-math' ),
			'desc'    => esc_html( $slack_enhanced_sharing_description ),
			'default' => in_array( $current_post_type, [ 'post', 'page', 'product', 'download' ], true ) ? 'on' : 'off',
			'classes' => 'rank-math-advanced-option',
		]
	);

	$cmb->add_field(
		[
			'id'      => 'pt_' . $current_post_type . '_add_meta_box',
			'type'    => 'toggle',
			'name'    => esc_html__( 'Add SEO Controls', 'seo-by-rank-math' ),
			'desc'    => esc_html__( 'Add SEO controls for the editor screen to customize SEO options for posts in this post type.', 'seo-by-rank-math' ),
			'default' => 'on',
			'classes' => 'rank-math-advanced-option',
		]
	);

	$cmb->add_field(
		[
			'id'      => 'pt_' . $current_post_type . '_bulk_editing',
			'type'    => 'radio_inline',
			'name'    => esc_html__( 'Bulk Editing', 'seo-by-rank-math' ),
			'desc'    => esc_html__( 'Add bulk editing columns to the post listing screen.', 'seo-by-rank-math' ),
			'options' => [
				'0'        => esc_html__( 'Disabled', 'seo-by-rank-math' ),
				'editing'  => esc_html__( 'Enabled', 'seo-by-rank-math' ),
				'readonly' => esc_html__( 'Read Only', 'seo-by-rank-math' ),
			],
			'default' => 'editing',
			'dep'     => [ [ 'pt_' . $current_post_type . '_add_meta_box', 'on' ] ],
			'classes' => 'rank-math-advanced-option',
		]
	);

	$cmb->add_field(
		[
			'id'      => 'pt_' . $current_post_type . '_analyze_fields',
			'type'    => 'textarea_small',
			'name'    => esc_html__( 'Custom Fields', 'seo-by-rank-math' ),
			'desc'    => esc_html__( 'List of custom fields name to include in the Page analysis. Add one per line.', 'seo-by-rank-math' ),
			'default' => '',
			'classes' => 'rank-math-advanced-option',
		]
	);
}

// Archive not enabled.
if ( ! $post_type_obj->has_archive ) {
	$cmb->remove_field( 'pt_' . $current_post_type . '_archive_title' );
	$cmb->remove_field( 'pt_' . $current_post_type . '_archive_description' );
	$cmb->remove_field( 'pt_' . $current_post_type . '_facebook_image' );
}

if ( 'attachment' === $current_post_type ) {
	$cmb->remove_field( 'pt_' . $current_post_type . '_link_suggestions' );
	$cmb->remove_field( 'pt_' . $current_post_type . '_ls_use_fk' );
}

if ( $is_stories_post_type ) {
	$cmb->remove_field( 'pt_' . $current_post_type . '_default_snippet_desc' );
	$cmb->remove_field( 'pt_' . $current_post_type . '_description' );
	$cmb->remove_field( 'pt_' . $current_post_type . '_link_suggestions' );
	$cmb->remove_field( 'pt_' . $current_post_type . '_ls_use_fk' );
	$cmb->remove_field( 'pt_' . $current_post_type . '_analyze_fields' );
	$cmb->remove_field( 'pt_' . $current_post_type . '_bulk_editing' );
	$cmb->remove_field( 'pt_' . $current_post_type . '_add_meta_box' );
}
