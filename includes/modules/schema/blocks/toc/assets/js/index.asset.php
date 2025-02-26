<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is required to dynamically load the necessary block dependencies.
/**
 * Block script dependencies.
 *
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

return [
	'dependencies' => [
		'wp-blocks',
		'wp-element',
		'wp-components',
		'wp-block-editor',
		'wp-data',
		'wp-dom',
		'wp-url',
		'wp-i18n',
		'lodash',
		'wp-primitives',
		'wp-reusable-blocks',
	],
	'version'      => rank_math()->version,
];
