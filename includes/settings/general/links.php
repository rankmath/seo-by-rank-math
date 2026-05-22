<?php
/**
 * The images settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;
use RankMath\KB;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'strip_category_base',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Strip Category Base', 'seo-by-rank-math' ),
		/* translators: Link to kb article */
		'desc'    => sprintf( wp_kses_post( __( 'Remove /category/ from category archive URLs. %s <br>E.g. <code>example.com/category/my-category/</code> becomes <code>example.com/my-category</code>', 'seo-by-rank-math' ) ), '<a href="' . KB::get( 'remove-category-base', 'Options Panel Strip Category Base' ) . '" target="_blank">' . esc_html__( 'Why do this?', 'seo-by-rank-math' ) . '</a>' ),
		'classes' => 'rank-math-advanced-option',
		'default' => 'off',
	]
);

$redirection_message = Helper::is_module_active( 'redirections' ) ?
	/* translators: Redirection page url */
	' <a href="' . Helper::get_admin_url( 'options-general#setting-panel-redirections' ) . '" target="new">' . esc_html__( 'Redirection Manager', 'seo-by-rank-math' ) . '</a>' :
	'<span class="rank-math-tooltip">' . esc_html__( 'Redirections Manager', 'seo-by-rank-math' ) . '<span>' . esc_html__( 'Please enable Redirections module.', 'seo-by-rank-math' ) . '</span></span>';


$cmb->add_field(
	[
		'id'      => 'attachment_redirect_urls',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Redirect Attachments', 'seo-by-rank-math' ),
		/* translators: Link to kb article */
		'desc'    => sprintf( wp_kses_post( __( 'Redirect all attachment page URLs to the post they appear in. For more advanced redirection control, use the built-in %s.', 'seo-by-rank-math' ) ), $redirection_message ),
		'default' => 'on',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'attachment_redirect_default',
		'type'    => 'text',
		'name'    => esc_html__( 'Redirect Orphan Attachments', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Redirect attachments without a parent post to this URL. Leave empty for no redirection.', 'seo-by-rank-math' ),
		'classes' => 'rank-math-advanced-option',
		'dep'     => [ [ 'attachment_redirect_urls', 'on' ] ],
	]
);

$cmb->add_field(
	[
		'id'      => 'nofollow_external_links',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Nofollow External Links', 'seo-by-rank-math' ),
		'desc'    => wp_kses_post( __( 'Automatically add <code>rel="nofollow"</code> attribute for external links appearing in your posts, pages, and other post types. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.', 'seo-by-rank-math' ) ),
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'nofollow_image_links',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Nofollow Image File Links', 'seo-by-rank-math' ),
		'desc'    => wp_kses_post( __( 'Automatically add <code>rel="nofollow"</code> attribute for links pointing to external image files. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.', 'seo-by-rank-math' ) ),
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'nofollow_domains',
		'type'    => 'textarea_small',
		'name'    => esc_html__( 'Nofollow Domains', 'seo-by-rank-math' ),
		'desc'    => wp_kses_post( __( 'Only add <code>nofollow</code> attribute for the link if target domain is in this list. Add one per line. Leave empty to apply nofollow for <strong>ALL</strong> external domains.', 'seo-by-rank-math' ) ),
		'classes' => 'rank-math-advanced-option',
		'dep'     => [
			[ 'nofollow_external_links', 'on' ],
			[ 'nofollow_image_links', 'on' ],
		],
	]
);

$cmb->add_field(
	[
		'id'      => 'nofollow_exclude_domains',
		'type'    => 'textarea_small',
		'name'    => esc_html__( 'Nofollow Exclude Domains', 'seo-by-rank-math' ),
		'desc'    => wp_kses_post( __( 'The <code>nofollow</code> attribute <strong>will not be added</strong> for the link if target domain is in this list. Add one per line.', 'seo-by-rank-math' ) ),
		'classes' => 'rank-math-advanced-option',
		'dep'     => [
			[ 'nofollow_external_links', 'on' ],
			[ 'nofollow_image_links', 'on' ],
		],
	]
);

$cmb->add_field(
	[
		'id'      => 'new_window_external_links',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Open External Links in New Tab/Window', 'seo-by-rank-math' ),
		'desc'    => wp_kses_post( __( 'Automatically add <code>target="_blank"</code> attribute for external links appearing in your posts, pages, and other post types to make them open in a new browser tab or window. The attribute is dynamically applied when the content is displayed, and the stored content is not changed.', 'seo-by-rank-math' ) ),
		'default' => 'on',
	]
);
