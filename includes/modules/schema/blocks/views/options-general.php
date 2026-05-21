<?php
/**
 * Blocks general settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Schema
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'toc_block_title',
		'type'    => 'text',
		'name'    => esc_html__( 'Table of Contents Title', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Enter the default title to use for the Table of Contents block.', 'seo-by-rank-math' ),
		'classes' => 'rank-math-advanced-option',
		'default' => esc_html__( 'Table of Contents', 'seo-by-rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'toc_block_list_style',
		'type'    => 'select',
		'name'    => esc_html__( 'Table of Contents List style', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Select the default list style for the Table of Contents block.', 'seo-by-rank-math' ),
		'options' => [
			'div' => esc_html__( 'None', 'seo-by-rank-math' ),
			'ol'  => esc_html__( 'Numbered', 'seo-by-rank-math' ),
			'ul'  => esc_html__( 'Unordered', 'seo-by-rank-math' ),
		],
		'default' => 'ul',
	]
);

$cmb->add_field(
	[
		'id'      => 'toc_block_exclude_headings',
		'name'    => esc_html__( 'Table of Contents Exclude Headings', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Choose the headings to exclude from the Table of Contents block.', 'seo-by-rank-math' ),
		'type'    => 'multicheck',
		'options' => [
			'h1' => esc_html__( 'Heading H1', 'seo-by-rank-math' ),
			'h2' => esc_html__( 'Heading H2', 'seo-by-rank-math' ),
			'h3' => esc_html__( 'Heading H3', 'seo-by-rank-math' ),
			'h4' => esc_html__( 'Heading H4', 'seo-by-rank-math' ),
			'h5' => esc_html__( 'Heading H5', 'seo-by-rank-math' ),
			'h6' => esc_html__( 'Heading H6', 'seo-by-rank-math' ),
		],
	]
);
