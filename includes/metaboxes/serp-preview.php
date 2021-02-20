<?php
/**
 * Serp Preview
 *
 * @package    RankMath
 * @subpackage RankMath\Metaboxes
 */

use RankMath\Admin\Serp_Preview;

defined( 'ABSPATH' ) || exit;

$checklist = new Serp_Preview();
$checklist->display();
