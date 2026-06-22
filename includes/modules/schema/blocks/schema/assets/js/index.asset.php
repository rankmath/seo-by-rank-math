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
		'wp-api-fetch',
		'wp-blocks',
		'wp-block-editor',
		'wp-components',
		'wp-compose',
		'wp-data',
		'wp-element',
		'wp-hooks',
		'wp-i18n',
		'wp-url',
	],
	'version'      => rank_math()->version,
];
