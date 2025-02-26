<?php
/**
 * The Breadcrumbs Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Frontend\Breadcrumbs as BreadcrumbTrail;
use RankMath\Paper\Paper;

defined( 'ABSPATH' ) || exit;

/**
 * Breadcrumbs class.
 */
class Breadcrumbs implements Snippet {

	/**
	 * Generate breadcrumbs JSON-LD.
	 *
	 * @link https://schema.org/BreadcrumbList
	 *
	 * @param array  $data Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$crumbs = BreadcrumbTrail::get() ? BreadcrumbTrail::get()->get_crumbs() : false;
		if ( empty( $crumbs ) ) {
			return $data;
		}

		$entity = [
			'@type'           => 'BreadcrumbList',
			'@id'             => Paper::get()->get_canonical() . '#breadcrumb',
			'itemListElement' => [],
		];

		$position = 1;
		foreach ( $crumbs as $crumb ) {
			if ( ! empty( $crumb['hide_in_schema'] ) || empty( $crumb[1] ) ) {
				continue;
			}

			$entity['itemListElement'][] = [
				'@type'    => 'ListItem',
				'position' => $position,
				'item'     => [
					'@id'  => $crumb[1],
					'name' => $crumb[0],
				],
			];

			++$position;
		}

		$entity = apply_filters( 'rank_math/snippet/breadcrumb', $entity );
		if ( empty( $entity['itemListElement'] ) ) {
			return $data;
		}

		$data['BreadcrumbList'] = $entity;
		return $data;
	}
}
