<?php
/**
 * Link Suggestions
 *
 * @package    RankMath
 * @subpackage RankMath\Metaboxes
 */

$suggestions = rank_math()->admin->get_link_suggestions( get_post() );
if ( empty( $suggestions ) ) {
	echo $field->args( 'not_found' );
	return;
}

echo rank_math()->admin->get_link_suggestions_html( $suggestions );
