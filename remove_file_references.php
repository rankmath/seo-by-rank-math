<?php
/**
 * Remove file references from pot file to reduce the file size.
 *
 * @since      1.0.207
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

$pot_file         = 'languages/rank-math.pot';
$filtered_content = preg_replace( '/^#\:\s.*\n/m', '', file_get_contents( $pot_file ) );
file_put_contents( $pot_file, $filtered_content );
