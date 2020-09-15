<?php
/**
 * The Video Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema\Video
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Video class.
 */
class Video implements Snippet {

	/**
	 * Video rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$entity = [
			'@type'       => 'VideoObject',
			'name'        => $jsonld->parts['title'],
			'description' => $jsonld->parts['desc'],
			'uploadDate'  => $jsonld->parts['published'],
		];

		if ( $duration = Helper::get_post_meta( 'snippet_video_duration' ) ) { // phpcs:ignore
			$entity['duration'] = Helper::get_formatted_duration( $duration );
		}

		$jsonld->set_data(
			[
				'snippet_video_url'       => 'contentUrl',
				'snippet_video_embed_url' => 'embedUrl',
				'snippet_video_views'     => 'interactionCount',
			],
			$entity
		);

		if ( isset( $data['Organization'] ) ) {
			$jsonld->set_publisher( $entity, $data['Organization'] );
			unset( $data['Organization'] );
		}

		return $entity;
	}
}
