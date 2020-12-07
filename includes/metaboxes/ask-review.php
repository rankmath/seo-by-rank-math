<?php
/**
 * Metabox - Review Tab
 *
 * @package    RankMath
 * @subpackage RankMath\Metaboxes
 */

use RankMath\Admin\Ask_Review;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'rank_math_ask_review',
		'type'    => 'raw',
		'content' => Ask_Review::display(),
	]
);
