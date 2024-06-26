<?php
/**
 * Link Suggestions
 *
 * @package    RankMath
 * @subpackage RankMath\Metaboxes
 */

defined( 'ABSPATH' ) || exit;

$suggestions = rank_math()->admin->get_link_suggestions( get_post() );
if ( empty( $suggestions ) ) {
	echo wp_kses_post( $field->args( 'not_found' ) );
	return;
}

echo wp_kses_post( rank_math()->admin->get_link_suggestions_html( $suggestions ) );
