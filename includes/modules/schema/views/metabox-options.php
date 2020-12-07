<?php
/**
 * Metabox - Schema Tab
 *
 * @package    RankMath
 * @subpackage RankMath\Schema
 */

use RankMath\KB;
use RankMath\Helper;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\WordPress;

if ( ! Helper::has_cap( 'onpage_snippet' ) ) {
	return;
}

$cmb->add_field(
	[
		'id'         => 'rank_math_schema_generator',
		'type'       => 'raw',
		'content'    => '<div id="rank-math-schema-generator"></div>',
		'save_field' => false,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank-math-schemas',
		'type'       => 'textarea',
		'classes'    => 'hidden',
		'save_field' => false,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank-math-schemas-delete',
		'type'       => 'textarea',
		'default'    => '[]',
		'classes'    => 'hidden',
		'save_field' => false,
	]
);
