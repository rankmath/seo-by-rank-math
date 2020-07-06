<?php
/**
 * Metabox - Schema Tab
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

use RankMath\KB;
use RankMath\Helper;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\WordPress;

if ( ! Helper::has_cap( 'onpage_snippet' ) ) {
	return;
}

$post_type = WordPress::get_post_type();

if ( ( class_exists( 'WooCommerce' ) && 'product' === $post_type ) || ( class_exists( 'Easy_Digital_Downloads' ) && 'download' === $post_type ) ) {

	$cmb->add_field(
		[
			'id'      => 'rank_math_woocommerce_notice',
			'type'    => 'notice',
			'what'    => 'info',
			'content' => '<span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Rank Math automatically inserts additional Schema meta data for WooCommerce products. You can set the Schema Type to "None" to disable this feature and just use the default data added by WooCommerce.', 'rank-math' ),
		]
	);

	$default = Helper::get_settings( "titles.pt_{$post_type}_default_rich_snippet" );
	$cmb->add_field(
		[
			'id'      => 'rank_math_rich_snippet',
			'type'    => 'radio_inline',
			'name'    => esc_html__( 'Schema Type', 'rank-math' ),
			/* translators: link to title setting screen */
			'desc'    => sprintf( wp_kses_post( __( 'Schema help you stand out in SERPs. <a href="%s" target="_blank">Learn more</a>.', 'rank-math' ) ), KB::get( 'rich-snippets' ) ),
			'options' => [
				'off'     => esc_html__( 'None', 'rank-math' ),
				'product' => esc_html__( 'Product', 'rank-math' ),
			],
			'default' => $default ? $default : 'off',
		]
	);

	return;
}

$has_reviews = Helper::get_review_posts();
$cmb->add_field(
	[
		'id'      => 'rank_math_rich_snippet',
		'type'    => 'select',
		'name'    => esc_html__( 'Schema Type', 'rank-math' ),
		/* translators: link to title setting screen */
		'desc'    => sprintf( wp_kses_post( __( 'Schema help you stand out in SERPs. <a href="%s" target="_blank">Learn more</a>.', 'rank-math' ) ), KB::get( 'rich-snippets' ) ),
		'options' => Helper::choices_rich_snippet_types( esc_html__( 'None', 'rank-math' ) ),
		'default' => ! Helper::can_use_default_schema( Param::get( 'post' ) ) ? 'off' : Helper::get_settings( "titles.pt_{$post_type}_default_rich_snippet" ),
	]
);

if ( $has_reviews ) {
	$cmb->add_field(
		[
			'id'      => 'rank_math_review_schema_notice',
			'type'    => 'notice',
			'what'    => 'error',
			'classes' => 'hidden',
			/* translators: link to Database Tools page */
			'content' => sprintf( wp_kses_post( __( 'Google does not support this Schema type anymore, please use different type or use <a href="%s" target="_blank">this tool</a> to convert all the old posts.', 'rank-math' ) ), Helper::get_admin_url( 'status', 'view=tools' ) ),
		]
	);
}

// Common fields.
$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_location',
		'name'    => esc_html__( 'Review Location', 'rank-math' ),
		'desc'    => esc_html__( 'The review or rating must be displayed on the page to comply with Google\'s Schema guidelines.', 'rank-math' ),
		'type'    => 'select',
		'dep'     => [ [ 'rank_math_rich_snippet', 'book,course,event,product,recipe,software', '=' ] ],
		'classes' => 'nob',
		'default' => 'custom',
		'options' => [
			'bottom' => esc_html__( 'Below Content', 'rank-math' ),
			'top'    => esc_html__( 'Above Content', 'rank-math' ),
			'both'   => esc_html__( 'Above & Below Content', 'rank-math' ),
			'custom' => esc_html__( 'Custom (use shortcode)', 'rank-math' ),
		],
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_shortcode',
		'name'       => esc_html__( 'Shortcode', 'rank-math' ),
		'type'       => 'text',
		'desc'       => esc_html__( 'Copy & paste this shortcode in the content.', 'rank-math' ),
		'save_field' => false,
		'dep'        => [
			'relation' => 'and',
			[ 'rank_math_rich_snippet', 'book,course,event,product,recipe,software' ],
			[ 'rank_math_snippet_location', 'custom' ],
		],
		'attributes' => [
			'readonly' => 'readonly',
			'value'    => '[rank_math_rich_snippet]',
		],
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_name',
		'type'            => 'text',
		'name'            => esc_html__( 'Headline', 'rank-math' ),
		'dep'             => [ [ 'rank_math_rich_snippet', 'off', '!=' ] ],
		'attributes'      => [ 'placeholder' => Helper::get_settings( "titles.pt_{$post_type}_default_snippet_name", '' ) ],
		'classes'         => 'rank-math-supports-variables',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_desc',
		'type'       => 'textarea',
		'name'       => esc_html__( 'Description', 'rank-math' ),
		'attributes' => [
			'rows'            => 3,
			'data-autoresize' => true,
			'placeholder'     => Helper::get_settings( "titles.pt_{$post_type}_default_snippet_desc", '' ),
		],
		'classes'    => 'rank-math-supports-variables',
		'dep'        => [ [ 'rank_math_rich_snippet', 'off,book,local', '!=' ] ],
		'escape_cb'  => 'esc_textarea',
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_url',
		'type'       => 'text_url',
		'name'       => esc_html__( 'URL', 'rank-math' ),
		'attributes' => [
			'rows'            => 3,
			'data-autoresize' => true,
			'data-rule-url'   => true,
		],
		'classes'    => 'rank-math-validate-field',
		'dep'        => [ [ 'rank_math_rich_snippet', 'book,local,music' ] ],
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_author',
		'type'       => 'text',
		'name'       => esc_html__( 'Author', 'rank-math' ),
		'attributes' => [
			'rows'            => 3,
			'data-autoresize' => true,
		],
		'dep'        => [ [ 'rank_math_rich_snippet', 'book' ] ],
	]
);

include_once 'article.php';
include_once 'book.php';
include_once 'course.php';
include_once 'event.php';
include_once 'job-posting.php';
include_once 'local.php';
include_once 'music.php';
include_once 'product.php';
include_once 'recipe.php';
include_once 'restaurant.php';
include_once 'video.php';
include_once 'person.php';
include_once 'review.php';
include_once 'software.php';
include_once 'service.php';
