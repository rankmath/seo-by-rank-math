<?php
/**
 * Metabox - Article Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

use RankMath\Helper;

$article_dep = [ [ 'rank_math_rich_snippet', 'article' ] ];
/* translators: Google article snippet doc link */
$article_desc = 'person' === Helper::get_settings( 'titles.knowledgegraph_type' ) ? '<div class="notice notice-warning inline rank-math-notice"><p>' . sprintf( __( 'Google does not allow Person as the Publisher for articles. Organization will be used instead. You can read more about this <a href="%s" target="_blank">here</a>.', 'rank-math' ), \RankMath\KB::get( 'article' ) ) . '</p></div>' : '';

$cmb->add_field([
	'id'      => 'rank_math_snippet_article_type',
	'type'    => 'radio_inline',
	'name'    => esc_html__( 'Article Type', 'rank-math' ),
	'options' => [
		'Article'     => esc_html__( 'Article', 'rank-math' ),
		'BlogPosting' => esc_html__( 'Blog Post', 'rank-math' ),
		'NewsArticle' => esc_html__( 'News Article', 'rank-math' ),
	],
	'default' => Helper::get_settings( "titles.pt_{$post_type}_default_article_type" ),
	'classes' => 'nob',
	'desc'    => $article_desc,
	'dep'     => $article_dep,
]);
