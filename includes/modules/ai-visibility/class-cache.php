<?php
/**
 * AI Visibility cache service.
 *
 * All locally stored AI Visibility data is temporary cache (non-autoloading
 * options + transients, no custom tables): the dashboard option (summary +
 * rollup rows + stale flags), per-brand queries options, and per-brand
 * identity/analysis transients. A changed `last_analyzed_at` marks a brand
 * stale; its analysis payload is refetched lazily on the next detail visit.
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\AI_Visibility
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\AI_Visibility;

defined( 'ABSPATH' ) || exit;

/**
 * Cache class.
 */
class Cache {

	/**
	 * Option key holding the dashboard (/overview) payload.
	 *
	 * @var string
	 */
	const DASHBOARD_KEY = 'rank_math_aiv_dashboard';

	/**
	 * Option key prefix for per-brand query lists.
	 *
	 * @var string
	 */
	const QUERIES_PREFIX = 'rank_math_aiv_queries_';

	/**
	 * Transient prefix for per-brand identity (incl. description).
	 *
	 * @var string
	 */
	const BRAND_PREFIX = 'rank_math_aiv_brand_';

	/**
	 * Transient prefix for per-brand full analysis payloads.
	 *
	 * @var string
	 */
	const ANALYSIS_PREFIX = 'rank_math_aiv_analysis_';

	/**
	 * Dashboard freshness window in seconds (filterable).
	 *
	 * @return int
	 */
	public static function dashboard_ttl() {
		return apply_filters( 'rank_math/ai_visibility/dashboard_ttl', 12 * HOUR_IN_SECONDS );
	}

	/**
	 * Queries list freshness window in seconds.
	 *
	 * @return int
	 */
	public static function queries_ttl() {
		return DAY_IN_SECONDS;
	}

	/**
	 * Transient expiry — garbage collection only, not a freshness rule.
	 *
	 * @return int
	 */
	public static function gc_expiry() {
		return 30 * DAY_IN_SECONDS;
	}

	// -------------------------------------------------------------------------
	// Dashboard (/overview) cache.
	// -------------------------------------------------------------------------

	/**
	 * Get the cached dashboard payload.
	 *
	 * @return array|null `[ 'summary' => [], 'brands' => [], 'fetched_at' => int ]` or null.
	 */
	public static function get_dashboard() {
		$data = get_option( self::DASHBOARD_KEY, null );
		return is_array( $data ) ? $data : null;
	}

	/**
	 * Whether the cached dashboard payload is still fresh.
	 *
	 * @return bool
	 */
	public static function is_dashboard_fresh() {
		$data = self::get_dashboard();
		if ( null === $data || empty( $data['fetched_at'] ) ) {
			return false;
		}

		return ( time() - (int) $data['fetched_at'] ) < self::dashboard_ttl();
	}

	/**
	 * Replace the dashboard cache; brands with a changed `last_analyzed`
	 * are flagged `stale` (existing flags carry over until cleared).
	 *
	 * @param array $summary Site-level summary metrics.
	 * @param array $brands  Per-brand rollup rows (UI shape).
	 *
	 * @return void
	 */
	public static function set_dashboard( $summary, $brands ) {
		$previous = self::get_dashboard();
		$old_rows = [];

		if ( null !== $previous && ! empty( $previous['brands'] ) ) {
			foreach ( $previous['brands'] as $row ) {
				if ( isset( $row['id'] ) ) {
					$old_rows[ $row['id'] ] = $row;
				}
			}
		}

		foreach ( $brands as &$row ) {
			$uuid = isset( $row['id'] ) ? $row['id'] : '';
			if ( '' === $uuid ) {
				continue;
			}

			$row['stale'] = false;

			if ( isset( $old_rows[ $uuid ] ) ) {
				$old = $old_rows[ $uuid ];

				// Carry over an uncleared stale flag.
				if ( ! empty( $old['stale'] ) ) {
					$row['stale'] = true;
				}

				// New analysis detected — flag the heavy payload for refetch.
				if ( ( $old['last_analyzed'] ?? null ) !== ( $row['last_analyzed'] ?? null ) ) {
					$row['stale'] = true;
				}
			}
		}
		unset( $row );

		update_option(
			self::DASHBOARD_KEY,
			[
				'summary'    => $summary,
				'brands'     => array_values( $brands ),
				'fetched_at' => time(),
			],
			false
		);
	}

	/**
	 * Append a brand row to the cached dashboard (after create).
	 *
	 * @param array $row Brand row in UI shape.
	 *
	 * @return void
	 */
	public static function append_brand_row( $row ) {
		$data = self::get_dashboard();
		if ( null === $data ) {
			return;
		}

		$row['stale']     = false;
		$data['brands'][] = $row;

		update_option( self::DASHBOARD_KEY, $data, false );
	}

	/**
	 * Patch fields of a single cached brand row in place.
	 *
	 * @param string $uuid   Brand UUID.
	 * @param array  $fields Fields to merge into the row.
	 *
	 * @return void
	 */
	public static function patch_brand_row( $uuid, $fields ) {
		$data = self::get_dashboard();
		if ( null === $data || empty( $data['brands'] ) ) {
			return;
		}

		foreach ( $data['brands'] as &$row ) {
			if ( isset( $row['id'] ) && $row['id'] === $uuid ) {
				$row = array_merge( $row, $fields );
				break;
			}
		}
		unset( $row );

		update_option( self::DASHBOARD_KEY, $data, false );
	}

	/**
	 * Get a single cached brand row.
	 *
	 * @param string $uuid Brand UUID.
	 *
	 * @return array|null
	 */
	public static function get_brand_row( $uuid ) {
		$data = self::get_dashboard();
		if ( null === $data || empty( $data['brands'] ) ) {
			return null;
		}

		foreach ( $data['brands'] as $row ) {
			if ( isset( $row['id'] ) && $row['id'] === $uuid ) {
				return $row;
			}
		}

		return null;
	}

	/**
	 * Whether a brand's full-analysis payload is flagged stale.
	 *
	 * @param string $uuid Brand UUID.
	 *
	 * @return bool
	 */
	public static function is_brand_stale( $uuid ) {
		$row = self::get_brand_row( $uuid );
		return null !== $row && ! empty( $row['stale'] );
	}

	/**
	 * Clear a brand's stale flag (after its analysis payload was refetched).
	 *
	 * @param string $uuid Brand UUID.
	 *
	 * @return void
	 */
	public static function clear_brand_stale( $uuid ) {
		self::patch_brand_row( $uuid, [ 'stale' => false ] );
	}

	// -------------------------------------------------------------------------
	// Per-brand identity (incl. description).
	// -------------------------------------------------------------------------

	/**
	 * Get the cached single-brand identity.
	 *
	 * @param string $uuid Brand UUID.
	 *
	 * @return array|false
	 */
	public static function get_brand( $uuid ) {
		return get_transient( self::BRAND_PREFIX . $uuid );
	}

	/**
	 * Cache the single-brand identity (write-through on GET and PATCH).
	 *
	 * @param string $uuid  Brand UUID.
	 * @param array  $brand Brand identity in UI shape.
	 *
	 * @return void
	 */
	public static function set_brand( $uuid, $brand ) {
		set_transient( self::BRAND_PREFIX . $uuid, $brand, self::gc_expiry() );
	}

	// -------------------------------------------------------------------------
	// Per-brand full analysis payload (competitors + transcripts).
	// -------------------------------------------------------------------------

	/**
	 * Get the cached full analysis payload for a brand.
	 *
	 * @param string $uuid Brand UUID.
	 *
	 * @return array|false
	 */
	public static function get_analysis( $uuid ) {
		return get_transient( self::ANALYSIS_PREFIX . $uuid );
	}

	/**
	 * Cache the full analysis payload for a brand and clear its stale flag.
	 *
	 * @param string $uuid     Brand UUID.
	 * @param array  $insights Insights payload in UI shape.
	 *
	 * @return void
	 */
	public static function set_analysis( $uuid, $insights ) {
		set_transient( self::ANALYSIS_PREFIX . $uuid, $insights, self::gc_expiry() );
		self::clear_brand_stale( $uuid );
	}

	// -------------------------------------------------------------------------
	// Per-brand query lists.
	// -------------------------------------------------------------------------

	/**
	 * Get the cached query list for a brand.
	 *
	 * @param string $uuid Brand UUID.
	 *
	 * @return array|null `[ 'queries' => [], 'fetched_at' => int ]` or null.
	 */
	public static function get_queries( $uuid ) {
		$data = get_option( self::QUERIES_PREFIX . $uuid, null );

		if ( ! is_array( $data ) || empty( $data['fetched_at'] ) ) {
			return null;
		}

		if ( ( time() - (int) $data['fetched_at'] ) >= self::queries_ttl() ) {
			return null;
		}

		return $data;
	}

	/**
	 * Cache the query list for a brand (full replace).
	 *
	 * @param string $uuid    Brand UUID.
	 * @param array  $queries Query items in UI shape.
	 *
	 * @return void
	 */
	public static function set_queries( $uuid, $queries ) {
		update_option(
			self::QUERIES_PREFIX . $uuid,
			[
				'queries'    => array_values( $queries ),
				'fetched_at' => time(),
			],
			false
		);
	}

	/**
	 * Patch a single query inside a brand's cached list.
	 *
	 * @param string $uuid     Brand UUID.
	 * @param string $query_id Query UUID.
	 * @param array  $fields   Fields to merge into the query.
	 *
	 * @return void
	 */
	public static function patch_query( $uuid, $query_id, $fields ) {
		$data = get_option( self::QUERIES_PREFIX . $uuid, null );
		if ( ! is_array( $data ) || empty( $data['queries'] ) ) {
			return;
		}

		foreach ( $data['queries'] as &$query ) {
			if ( isset( $query['id'] ) && $query['id'] === $query_id ) {
				$query = array_merge( $query, $fields );
				break;
			}
		}
		unset( $query );

		update_option( self::QUERIES_PREFIX . $uuid, $data, false );
	}
}
