<?php
/**
 * The Yoast Local Business Block Converter.
 *
 * @since      1.0.48
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Yoast_Local_Converter class.
 */
class Yoast_Local_Converter {

	/**
	 * Yoast's Local Business Blocks.
	 *
	 * @var array
	 */
	public $yoast_blocks = [
		'yoast-seo-local/store-locator',
		'yoast-seo-local/address',
		'yoast-seo-local/map',
		'yoast-seo-local/opening-hours',
	];

	/**
	 * Convert Local Business blocks to Rank Math.
	 *
	 * @param array $block Block to convert.
	 *
	 * @return array
	 */
	public function convert( $block ) {
		$block['attrs']['type'] = str_replace( 'yoast-seo-local/', '', $block['blockName'] );

		$new_block = [
			'blockName' => 'rank-math/local-business',
			'attrs'     => $this->get_attributes( $block['attrs'] ),
		];

		$new_block['innerContent'] = '';

		return $new_block;
	}

	/**
	 * Replace block in content.
	 *
	 * @param string $post_content Post content.
	 * @param array  $blocks       Blocks.
	 *
	 * @return string
	 */
	public function replace( $post_content, $blocks ) {
		foreach ( $blocks as $block_name => $block ) {
			if ( ! in_array( $block_name, $this->yoast_blocks, true ) ) {
				continue;
			}

			$block_name = str_replace( 'yoast-seo-local/', '', $block_name );
			preg_match_all( "/<!-- wp:yoast-seo-local\/{$block_name}.*-->.*<!-- \/wp:yoast-seo-local\/{$block_name} -->/iUs", $post_content, $matches );
			foreach ( $matches[0] as $index => $match ) {
				$post_content = \str_replace( $match, $block[ $index ], $post_content );
			}
		}

		return $post_content;
	}

	/**
	 * Get Block attributes.
	 *
	 * @param array $attrs Yoast Block Attributes.
	 */
	private function get_attributes( $attrs ) {
		$default_opening_days = 'Monday,	Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday';
		if ( 'opening-hours' === $attrs['type'] ) {
			$days         = explode( ', ', $default_opening_days );
			$default_days = [];

			foreach ( $days as $day ) {
				if ( isset( $attrs[ "show{$day}" ] ) ) {
					$default_days[ $day ] = ! empty( $attrs[ "show{$day}" ] );
				}
			}

			if ( ! empty( $default_days ) ) {
				$default_opening_days = implode( ',', array_filter( array_keys( $default_days ) ) );
			}
		}
		return [
			'type'                   => isset( $attrs['type'] ) ? $attrs['type'] : 'address',
			'locations'              => 0,
			'terms'                  => [],
			'limit'                  => isset( $attrs['maxNumber'] ) ? $attrs['maxNumber'] : 0,
			'show_company_name'      => isset( $attrs['hideName'] ) ? ! $attrs['hideName'] : true,
			'show_company_address'   => isset( $attrs['hideCompanyAddress'] ) ? ! $attrs['hideCompanyAddress'] : true,
			'show_on_one_line'       => isset( $attrs['showOnOneLine'] ) ? ! $attrs['showOnOneLine'] : false,
			'show_state'             => isset( $attrs['showState'] ) ? $attrs['showState'] : true,
			'show_country'           => isset( $attrs['showCountry'] ) ? $attrs['showCountry'] : true,
			'show_telephone'         => isset( $attrs['showPhone'] ) ? $attrs['showPhone'] : true,
			'show_secondary_number'  => isset( $attrs['showPhone2nd'] ) ? $attrs['showPhone2nd'] : true,
			'show_fax'               => isset( $attrs['showFax'] ) ? $attrs['showFax'] : false,
			'show_email'             => isset( $attrs['showEmail'] ) ? $attrs['showEmail'] : true,
			'show_url'               => isset( $attrs['showURL'] ) ? $attrs['showURL'] : true,
			'show_logo'              => isset( $attrs['showLogo'] ) ? $attrs['showLogo'] : true,
			'show_vat_id'            => isset( $attrs['showVatId'] ) ? $attrs['showVatId'] : false,
			'show_tax_id'            => isset( $attrs['showTaxId'] ) ? $attrs['showTaxId'] : false,
			'show_coc_id'            => isset( $attrs['showCocId'] ) ? $attrs['showCocId'] : false,
			'show_priceRange'        => isset( $attrs['showPriceRange'] ) ? $attrs['showPriceRange'] : false,
			'show_opening_hours'     => isset( $attrs['showOpeningHours'] ) ? $attrs['showOpeningHours'] : false,
			'show_days'              => $default_opening_days,
			'hide_closed_days'       => isset( $attrs['hideClosedDays'] ) ? $attrs['hideClosedDays'] : false,
			'show_opening_now_label' => isset( $attrs['showOpenLabel'] ) ? $attrs['showOpenLabel'] : false,
			'opening_hours_note'     => isset( $attrs['extraComment'] ) ? $attrs['extraComment'] : '',
			'show_map'               => isset( $attrs['showMap'] ) ? $attrs['showMap'] : false,
			'map_type'               => isset( $attrs['mapType'] ) ? $attrs['mapType'] : 'roadmap',
			'map_width'              => isset( $attrs['mapWidth'] ) ? $attrs['mapWidth'] : '500',
			'map_height'             => isset( $attrs['mapHeight'] ) ? $attrs['mapHeight'] : '300',
			'zoom_level'             => isset( $attrs['zoomLevel'] ) ? $attrs['zoomLevel'] : -1,
			'allow_zoom'             => true,
			'allow_scrolling'        => isset( $attrs['allowScrolling'] ) ? $attrs['allowScrolling'] : true,
			'allow_dragging'         => isset( $attrs['allowDragging'] ) ? $attrs['allowDragging'] : true,
			'show_route_planner'     => isset( $attrs['showRoute'] ) ? $attrs['showRoute'] : true,
			'route_label'            => Helper::get_settings( 'titles.route_label' ),
			'show_category_filter'   => isset( $attrs['showCategoryFilter'] ) ? $attrs['showCategoryFilter'] : false,
			'show_marker_clustering' => isset( $attrs['markerClustering'] ) ? $attrs['markerClustering'] : true,
			'show_infowindow'        => isset( $attrs['defaultShowInfoWindow'] ) ? $attrs['defaultShowInfoWindow'] : true,
			'show_radius'            => isset( $attrs['showRadius'] ) ? $attrs['showRadius'] : true,
			'show_nearest_location'  => isset( $attrs['showNearest'] ) ? $attrs['showNearest'] : true,
			'search_radius'          => isset( $attrs['searchRadius'] ) ? $attrs['searchRadius'] : '10',
		];
	}
}
