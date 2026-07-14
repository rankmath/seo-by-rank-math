<?php
/**
 * Top-keywords runner for the rank-math/get-top-keywords ability.
 *
 * @since      1.0.274
 * @package    RankMath
 * @subpackage RankMath\Abilities\Analytics
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Analytics;

use RankMath\Analytics\Stats;
use RankMath\Google\Console;

defined( 'ABSPATH' ) || exit;

/**
 * Fetches top-performing keywords from Google Search Console and shapes the
 * result for ability/MCP consumers.
 */
class Top_Keywords_Runner {

	/**
	 * Stats service instance.
	 *
	 * @var Stats
	 */
	private $stats;

	/**
	 * Constructor.
	 *
	 * @param Stats|null $stats Stats instance; defaults to the singleton.
	 */
	public function __construct( ?Stats $stats = null ) {
		$this->stats = $stats ?? Stats::get();
	}

	/**
	 * Fetch and return the top keywords for the given date range.
	 *
	 * The keywords array is passed through the rank_math/analytics/ability/top_keywords
	 * filter so that the PRO plugin can annotate entries with position-trend data.
	 *
	 * @param string $date_range One of the Get_Top_Keywords::DATE_RANGE_MAP keys.
	 * @param int    $limit      Maximum number of keywords to return.
	 * @return array {
	 *     @type array  $keywords   Flat list of keyword objects.
	 *     @type string $date_range The date_range value that was used.
	 *     @type bool   $connected  Whether Google Search Console is connected.
	 * }
	 */
	public function run( string $date_range, int $limit ): array {
		if ( ! Console::is_console_connected() ) {
			return [
				'keywords'   => [],
				'date_range' => $date_range,
				'connected'  => false,
			];
		}

		$range = isset( Get_Top_Keywords::DATE_RANGE_MAP[ $date_range ] )
			? Get_Top_Keywords::DATE_RANGE_MAP[ $date_range ]
			: Get_Top_Keywords::DATE_RANGE_MAP['last_30_days'];

		$this->stats->set_date_range( $range );

		$rows = $this->stats->get_analytics_data(
			[
				'dimension' => 'query',
				'orderBy'   => 'clicks',
				'order'     => 'DESC',
				'offset'    => 0,
				'perpage'   => $limit,
				'objects'   => true,
				'pageview'  => false,
			]
		);

		$keywords = [];
		foreach ( $rows as $query => $row ) {
			$keywords[] = [
				'keyword'     => (string) $query,
				'clicks'      => (int) $row['clicks']['total'],
				'impressions' => (int) $row['impressions']['total'],
				'ctr'         => (float) $row['ctr']['total'],
				'position'    => (float) $row['position']['total'],
			];
		}

		// PRO plugin hooks here to annotate keywords with position trend data.
		$keywords = apply_filters( 'rank_math/analytics/ability/top_keywords', $keywords, $date_range );

		return [
			'keywords'   => $keywords,
			'date_range' => $date_range,
			'connected'  => true,
		];
	}
}
