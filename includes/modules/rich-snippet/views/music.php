<?php
/**
 * Metabox - Music Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$music = [ [ 'rank_math_rich_snippet', 'music' ] ];

$cmb->add_field([
	'id'      => 'rank_math_snippet_music_type',
	'type'    => 'radio_inline',
	'name'    => esc_html__( 'Type', 'rank-math' ),
	'options' => [
		'MusicGroup' => esc_html__( 'MusicGroup', 'rank-math' ),
		'MusicAlbum' => esc_html__( 'MusicAlbum', 'rank-math' ),
	],
	'classes' => 'cmb-row-33 nob',
	'default' => 'MusicGroup',
	'dep'     => $music,
]);
