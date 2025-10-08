<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is required to dynamically load the necessary block dependencies.
/**
 * Block script dependencies.
 *
 * @package    RankMath
 * @subpackage RankMath\ContentAI
 * @author     Rank Math <support@rankmath.com>
 */

return [
	'dependencies' => [
		'wp-element',
		'wp-block-editor',
		'lodash',
	],
	'version'      => rank_math()->version,
];
