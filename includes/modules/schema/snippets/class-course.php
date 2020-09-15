<?php
/**
 * The Course Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Course class.
 */
class Course implements Snippet {

	/**
	 * Course rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$entity = [
			'@type'       => 'Course',
			'name'        => $jsonld->parts['title'],
			'description' => $jsonld->parts['desc'],
			'provider'    => [
				'@type'  => Helper::get_post_meta( 'snippet_course_provider_type' ) ? Helper::get_post_meta( 'snippet_course_provider_type' ) : 'Organization',
				'name'   => Helper::get_post_meta( 'snippet_course_provider' ),
				'sameAs' => Helper::get_post_meta( 'snippet_course_provider_url' ),
			],
		];

		$jsonld->add_ratings( 'course', $entity );
		if ( isset( $data['Organization'] ) ) {
			unset( $data['Organization'] );
		}

		return $entity;
	}
}
