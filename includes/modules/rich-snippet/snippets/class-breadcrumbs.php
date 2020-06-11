<?php
/**
 * The Breadcrumbs Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\RichSnippet;

use RankMath\Frontend\Breadcrumbs as BreadcrumbTrail;

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
	 * @param array  $data   Array of JSON-LD data.
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
			'itemListElement' => [],
		];

		foreach ( $crumbs as $index => $crumb ) {
			if ( ! empty( $crumb['hide_in_schema'] ) ) {
				continue;
			}

			$entity['itemListElement'][] = [
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'item'     => [
					'@id'  => $crumb[1],
					'name' => $crumb[0],
				],
			];
		}

		$data['BreadcrumbList'] = apply_filters( 'rank_math/snippet/breadcrumb', $entity );

		return $data;
	}
}
