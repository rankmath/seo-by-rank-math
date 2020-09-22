<?php
/**
 * The Publisher Class.
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
 * Publisher class.
 */
class Publisher implements Snippet {

	/**
	 * PrimaryImage rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$type              = Helper::get_settings( 'titles.knowledgegraph_type' );
		$data['publisher'] = [
			'@type' => 'person' === $type ? 'Person' : 'Organization',
			'@id'   => home_url( "/#{$type}" ),
			'name'  => $jsonld->get_website_name(),
			'logo'  => [
				'@type' => 'ImageObject',
				'url'   => Helper::get_settings( 'titles.knowledgegraph_logo' ),
			],
		];

		if ( 'person' === $type ) {
			$data['publisher']['image'] = $data['publisher']['logo'];
			unset( $data['publisher']['logo'] );
		}

		return $data;
	}
}
