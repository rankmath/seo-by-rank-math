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
		'wp-block-editor',
		'wp-components',
		'wp-data',
		'wp-element',
		'wp-hooks',
		'wp-i18n',
		'lodash',
	],
	'version'      => rank_math()->version,
];
