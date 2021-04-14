<?php
/**
 * The Primary Image Class.
 *
 * @since      1.0.43
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * PrimaryImage class.
 */
class PrimaryImage implements Snippet {

	/**
	 * Add primaryImage entity in JSON-LD data.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$image = Helper::get_thumbnail_with_fallback( get_the_ID(), 'full' );
		if ( empty( $image ) ) {
			return $data;
		}

		$data['primaryImage'] = [
			'@type'   => 'ImageObject',
			'@id'     => $image[0],
			'url'     => $image[0],
			'width'   => $image[1],
			'height'  => $image[2],
			'caption' => isset( $image['caption'] ) ? $image['caption'] : '',
		];

		$jsonld->add_prop( 'language', $data['primaryImage'] );

		return $data;
	}
}
