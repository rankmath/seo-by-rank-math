<?php
/**
 * Content AI general settings.
 *
 * @package    RankMath
 * @subpackage RankMath\ContentAI
 */

use RankMath\Helper;
use RankMath\KB;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'content_ai_country',
		'type'    => 'select',
		'name'    => esc_html__( 'Default Country', 'rank-math' ),
		'options' => Helper::choices_contentai_countries(),
		'default' => 'all',
	]
);

$post_types = Helper::choices_post_types();
if ( isset( $post_types['attachment'] ) ) {
	unset( $post_types['attachment'] );
}

$cmb->add_field(
	[
		'id'      => 'content_ai_post_types',
		'type'    => 'multicheck_inline',
		'name'    => esc_html__( 'Select Post Type', 'rank-math' ),
		'desc'    => esc_html__( 'Select the post type you use for Content AI.', 'rank-math' ),
		'options' => $post_types,
		'default' => array_keys( $post_types ),
	]
);

$credits = get_option( 'rank_math_ca_credits' );
if ( false !== $credits ) {
	$update_credits = '<a href="#" class="rank-math-tooltip update-credit">
		<i class="dashicons dashicons-image-rotate"></i>
		<span>' . esc_html__( 'Click to refresh the available credits.', 'rank-math' ) . '</span>
	</a>';

	$cmb->add_field(
		[
			'id'      => 'content_ai_credits',
			'type'    => 'raw',
			/* translators: 1. Credits left 2. Buy more credits link */
			'content' => '<div class="cmb-row buy-more-credits rank-math-exclude-from-search">' . $update_credits . sprintf( esc_html__( '%1$s credits left. Upgrade to get more credits from %2$s.', 'rank-math' ), '<strong>' . $credits . '</strong>', '<a href="' . KB::get( 'content-ai-pricing-tables', 'Buy CAI Credits Options Panel' ) . '" target="_blank">' . esc_html__( 'here', 'rank-math' ) . '</a>' ) . '</div>',
		]
	);
}
