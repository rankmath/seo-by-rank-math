<?php
/**
 * The Service Class.
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
 * Service class.
 */
class Service implements Snippet {

	/**
	 * Service rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$price  = Helper::get_post_meta( 'snippet_service_price' );
		$entity = [
			'@type'       => 'Service',
			'name'        => $jsonld->parts['title'],
			'description' => $jsonld->parts['desc'],
			'serviceType' => Helper::get_post_meta( 'snippet_service_type' ),
			'offers'      => [
				'@type'         => 'Offer',
				'price'         => $price ? $price : '0',
				'priceCurrency' => Helper::get_post_meta( 'snippet_service_price_currency' ),
			],
		];

		return $entity;
	}
}
