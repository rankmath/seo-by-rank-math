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
		'name'    => esc_html__( 'Table of Contents Title', 'rank-math' ),
		'desc'    => esc_html__( 'Enter the default title to use for the Table of Contents block.', 'rank-math' ),
		'classes' => 'rank-math-advanced-option',
		'default' => esc_html__( 'Table of Contents', 'rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'toc_block_list_style',
		'type'    => 'select',
		'name'    => esc_html__( 'Table of Contents List style', 'rank-math' ),
		'desc'    => esc_html__( 'Select the default list style for the Table of Contents block.', 'rank-math' ),
		'options' => [
			'div' => esc_html__( 'None', 'rank-math' ),
			'ol'  => esc_html__( 'Numbered', 'rank-math' ),
			'ul'  => esc_html__( 'Unordered', 'rank-math' ),
		],
		'default' => 'unordered',
	]
);

$cmb->add_field(
	[
		'id'      => 'toc_block_exclude_headings',
		'name'    => esc_html__( 'Table of Contents Exclude Headings', 'rank-math' ),
		'desc'    => esc_html__( 'Choose the headings to exclude from the Table of Contents block.', 'rank-math' ),
		'type'    => 'multicheck',
		'options' => [
			'h1' => esc_html__( 'Heading H1', 'rank-math' ),
			'h2' => esc_html__( 'Heading H2', 'rank-math' ),
			'h3' => esc_html__( 'Heading H3', 'rank-math' ),
			'h4' => esc_html__( 'Heading H4', 'rank-math' ),
			'h5' => esc_html__( 'Heading H5', 'rank-math' ),
			'h6' => esc_html__( 'Heading H6', 'rank-math' ),
		],
	]
);
